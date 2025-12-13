<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('olx:clear-cache')
                ->dailyAt('01:55')
                ->withoutOverlapping()
                ->onOneServer();

            $schedule->command('olx:sync-categories --force')
                ->dailyAt('02:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->runInBackground();

            $schedule->command('olx:sync-category-fields --force')
                ->dailyAt('03:00')
                ->withoutOverlapping()
                ->onOneServer()
                ->runInBackground();
        });
    }
}
