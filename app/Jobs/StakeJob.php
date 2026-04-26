<?php

namespace App\Jobs;

use App\Models\SavingsEntry;
use App\Services\SuiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StakeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 45;

    public function __construct(public SavingsEntry $entry) {}

    public function handle(SuiService $suiService): void
    {
        $user = $this->entry->user;

        if (!$user->sui_finance_profile_id) return;

        $vaultId = config('sui.vault_object_id');
        
        // Prevent stake if no vault ID is configured in .env
        if (!$vaultId || str_contains($vaultId, 'YOUR_VAULT_OBJECT_ID')) return;

        $amount  = (float)$this->entry->amount + (float)$this->entry->round_up_amount;

        $result = $suiService->addSavingsAndStake(
            $user->sui_finance_profile_id,
            $vaultId,
            $amount
        );

        if ($result) {
            $this->entry->update([
                'staked'         => true,
                'stake_digest'   => $result['digest'],
                'synced_on_chain' => true,
                'sui_digest'     => $result['digest'],
            ]);
        } else {
            $this->fail(new \RuntimeException('StakeJob: SuiService returned null'));
        }
    }
}
