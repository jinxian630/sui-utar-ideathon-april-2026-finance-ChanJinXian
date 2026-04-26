<?php

namespace App\Jobs;

use App\Models\SavingsEntry;
use App\Services\SuiService;
use App\Services\BadgeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSavingsToChainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(public SavingsEntry $entry) {}

    public function handle(SuiService $suiService, BadgeService $badgeService): void
    {
        $user = $this->entry->user;

        if (!$user->sui_finance_profile_id) {
            Log::info("SyncSavingsToChainJob: user {$user->id} has no Sui profile, skipping.");
            return;
        }

        // Effective amount = amount + round_up_amount
        $effectiveAmount = (float)$this->entry->amount + (float)$this->entry->round_up_amount;

        $digest = $suiService->addSavings(
            profileObjectId: $user->sui_finance_profile_id,
            amount:          $effectiveAmount
        );

        if ($digest) {
            $this->entry->update([
                'synced_on_chain' => true,
                'sui_digest'      => $digest,
            ]);

            if ($this->entry->goal_id && $this->entry->type === 'income') {
                $badgeService->checkMilestonesAndAwardRebate($user);
            }

            Log::info("SyncSavingsToChainJob: success", [
                'entry_id' => $this->entry->id,
                'digest'   => $digest,
            ]);
        } else {
            $this->fail(new \RuntimeException("SuiService::addSavings returned null digest"));
        }
    }
}
