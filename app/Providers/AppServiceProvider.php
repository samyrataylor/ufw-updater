<?php

namespace App\Providers;

use App\Commands\InspireCommand;
use App\Commands\Tinker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            config([
                'commands.remove' => [
                    Tinker::class,
                ],
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
