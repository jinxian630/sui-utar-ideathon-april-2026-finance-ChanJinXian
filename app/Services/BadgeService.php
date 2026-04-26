<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    // ── Milestone Tier Table ─────────────────────────────────────────────────
    // Keys match the slug stored in badges.slug and passed to the Move contract.
    // threshold: minimum RM total_saved to unlock the tier
    // base_bps:  base rate in basis points (100 bps = 1%)
    // bonus_bps: additional bonus basis points for this tier
    private array $milestones = [
        ['slug' => 'saver',          'level' => 1, 'threshold' => 100,   'base_bps' => 100, 'bonus_bps' => 0],
        ['slug' => 'investor',       'level' => 2, 'threshold' => 500,   'base_bps' => 100, 'bonus_bps' => 50],
        ['slug' => 'wealth_builder', 'level' => 3, 'threshold' => 1000,  'base_bps' => 100, 'bonus_bps' => 100],
        ['slug' => 'diamond_saver',  'level' => 4, 'threshold' => 5000,  'base_bps' => 100, 'bonus_bps' => 200],
        ['slug' => 'finance_master', 'level' => 5, 'threshold' => 10000, 'base_bps' => 100, 'bonus_bps' => 400],
    ];

    private array $newlyAwardedBadges = [];

    public function __construct(private SuiService $suiService) {}

    /**
     * Check if the user has crossed any new milestone thresholds.
     * Mints badge NFT on-chain (which also calculates the rebate in the Move contract)
     * and credits the rebate to users.wallet_balance.
     *
     * @return float  Total rebate earned in RM this cycle (for flash messages)
     */
    public function checkMilestonesAndAwardRebate(User $user): float
    {
        $this->newlyAwardedBadges = [];

        // Badge progress is based only on savings deposited into goals.
        $goalDeposits = \App\Models\SavingsEntry::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereNotNull('goal_id')
            ->sum(\Illuminate\Support\Facades\DB::raw('amount + COALESCE(round_up_amount, 0)'));

        $totalSaved = max(0, $goalDeposits);

        $totalRebate = 0.0;

        // Track which milestones were newly crossed this month (for stacking)
        $newlyMintedThisMonth = $user->badges()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        foreach ($this->milestones as $milestone) {
            if ($totalSaved < $milestone['threshold']) continue;
            if ($this->hasBadge($user, $milestone['slug'])) continue;

            // Determine stacking multiplier: 1.2× if user already earned a badge this month
            $isStacked = $newlyMintedThisMonth > 0;

            // Calculate the RM rebate locally (mirrors the Move contract formula)
            $rebateRm = $this->calculateRebate(
                $milestone['threshold'],
                $milestone['base_bps'],
                $milestone['bonus_bps'],
                $isStacked
            );

            // Call on-chain: mint badge NFT + emit RebateIssued event
            $mintResult = $this->suiService->claimMilestoneBadge(
                recipientAddress: $user->wallet_address ?? $user->sui_address ?? '',
                tierSlug:        $milestone['slug'],
                tierLevel:       $milestone['level'],
                thresholdCents:  (int) ($milestone['threshold'] * 100),
                tierBonusBps:    $milestone['bonus_bps'],
                isStacked:       $isStacked
            );

            // Record badge in DB (even if chain call fails, record locally)
            $badge = Badge::create([
                'user_id'    => $user->id,
                'slug'       => $milestone['slug'],
                'name'       => $this->tierName($milestone['slug']),
                'threshold'  => $milestone['threshold'],
                'level'      => $milestone['level'],
                'sui_digest' => $mintResult['digest'] ?? null,
                'sui_object_id' => $mintResult['badge_object_id'] ?? null,
                'suivision_url' => $mintResult['suivision_url'] ?? null,
            ]);

            $this->newlyAwardedBadges[] = [
                'name' => $badge->name,
                'level' => $badge->level,
                'objectId' => $badge->sui_object_id,
                'link' => $badge->suivision_url,
                'imageUrl' => '/images/badges/level-' . $badge->level . '.png',
            ];

            // Credit rebate to wallet_balance in DB
            DB::table('users')
                ->where('id', $user->id)
                ->increment('wallet_balance', $rebateRm);
            DB::table('users')
                ->where('id', $user->id)
                ->increment('rebate_earned', $rebateRm);

            $totalRebate += $rebateRm;
            $newlyMintedThisMonth++;

            Log::info("Badge minted for user {$user->id}: {$milestone['slug']}, rebate RM {$rebateRm}", [
                'stacked' => $isStacked,
                'digest'  => $mintResult['digest'] ?? null,
                'object_id' => $mintResult['badge_object_id'] ?? null,
            ]);
        }

        return round($totalRebate, 2);
    }

    public function newlyAwardedBadges(): array
    {
        return $this->newlyAwardedBadges;
    }

    public function publishMissingOnChainBadges(?User $targetUser = null): int
    {
        $query = Badge::query()
            ->whereNull('sui_object_id')
            ->with('user');

        if ($targetUser) {
            $query->where('user_id', $targetUser->id);
        }

        $published = 0;

        foreach ($query->get() as $badge) {
            $user = $badge->user;
            $milestone = $this->milestoneForSlug($badge->slug);

            if (!$user || !$milestone) {
                continue;
            }

            $mintResult = $this->suiService->claimMilestoneBadge(
                recipientAddress: $user->wallet_address ?? $user->sui_address ?? '',
                tierSlug: $milestone['slug'],
                tierLevel: $milestone['level'],
                thresholdCents: (int) ($milestone['threshold'] * 100),
                tierBonusBps: $milestone['bonus_bps'],
                isStacked: false
            );

            if (empty($mintResult['badge_object_id'])) {
                continue;
            }

            $badge->forceFill([
                'level' => $milestone['level'],
                'sui_digest' => $mintResult['digest'] ?? $badge->sui_digest,
                'sui_object_id' => $mintResult['badge_object_id'],
                'suivision_url' => $mintResult['suivision_url'],
            ])->save();

            $published++;
        }

        return $published;
    }

    /**
     * Calculate RM rebate.
     * Formula: target × (base_bps + bonus_bps) / 10000
     * Stacking multiplier: ×1.2
     */
    private function calculateRebate(float $threshold, int $baseBps, int $bonusBps, bool $isStacked): float
    {
        $rebate = $threshold * ($baseBps + $bonusBps) / 10000;
        return $isStacked ? round($rebate * 1.2, 2) : round($rebate, 2);
    }

    private function hasBadge(User $user, string $slug): bool
    {
        return $user->badges()->where('slug', $slug)->exists();
    }

    private function milestoneForSlug(string $slug): ?array
    {
        foreach ($this->milestones as $milestone) {
            if ($milestone['slug'] === $slug) {
                return $milestone;
            }
        }

        return null;
    }

    private function tierName(string $slug): string
    {
        return match($slug) {
            'saver'          => 'Saver — Level 1',
            'investor'       => 'Investor — Level 2',
            'wealth_builder' => 'Wealth Builder — Level 3',
            'diamond_saver'  => 'Diamond Saver — Level 4',
            'finance_master' => 'Finance Master — Level 5',
            default          => ucfirst($slug),
        };
    }

    /** @deprecated Use checkMilestonesAndAwardRebate() */
    public function checkAndMintBadge(User $user): void
    {
        $this->checkMilestonesAndAwardRebate($user);
    }
}
