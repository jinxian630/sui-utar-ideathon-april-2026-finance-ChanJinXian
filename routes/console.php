<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Services\BadgeService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sui:publish-missing-badges {userId?}', function (BadgeService $badgeService, ?int $userId = null) {
    $user = $userId ? User::find($userId) : null;

    if ($userId && !$user) {
        $this->error("User {$userId} was not found.");
        return self::FAILURE;
    }

    $published = $badgeService->publishMissingOnChainBadges($user);

    if ($published === 0) {
        $this->warn('No badges were published. Check SUI_PACKAGE_ID and storage/logs/laravel.log for Sui errors.');
        return self::SUCCESS;
    }

    $this->info("Published {$published} badge(s) to Sui Testnet.");
    return self::SUCCESS;
})->purpose('Mint local badges that are missing Sui Testnet object IDs');
