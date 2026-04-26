<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    public function recordRoundUp(User $user): void
    {
        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        if ($user->last_round_up_date === $today) {
            return; // already counted for today
        }

        if ($user->last_round_up_date === $yesterday) {
            $user->increment('round_up_streak'); // continuing
        } else {
            $user->round_up_streak = 1; // reset
        }

        $user->last_round_up_date = $today;
        $user->save();
    }
}
