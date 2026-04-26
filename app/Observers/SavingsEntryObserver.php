<?php

namespace App\Observers;

use App\Models\SavingsEntry;
use App\Services\SavingsService;

class SavingsEntryObserver
{
    public function __construct(protected SavingsService $savingsService) {}

    public function created(SavingsEntry $entry): void  { $this->resync($entry); }
    public function updated(SavingsEntry $entry): void  { $this->resync($entry); }
    public function deleted(SavingsEntry $entry): void  { $this->resync($entry); }
    public function restored(SavingsEntry $entry): void { $this->resync($entry); }
    public function forceDeleted(SavingsEntry $entry): void { $this->resync($entry); }

    private function resync(SavingsEntry $entry): void
    {
        $this->savingsService->syncUserTotal($entry->user_id);

        if ($entry->goal_id) {
            $this->savingsService->syncGoalTotal($entry->goal_id);
        }
    }
}
