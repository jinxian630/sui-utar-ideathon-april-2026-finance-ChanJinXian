<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            $appUrl = (string) config('app.url');

            if ($appUrl !== '') {
                URL::forceRootUrl(preg_replace('/^http:\/\//i', 'https://', $appUrl));
            }

            URL::forceScheme('https');
        }

        \App\Models\SavingsEntry::observe(\App\Observers\SavingsEntryObserver::class);
    }
}
