<?php

namespace Junges\Watchdog;

use Illuminate\Support\ServiceProvider;
use Junges\Watchdog\Console\Commands\DeleteExpiredInvitesCommand;

class WatchdogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishesConfig();

        $this->loadMigrations();

        $this->loadCommands();
    }

    /**
     * Load and publishes the package configuration file.
     */
    private function publishesConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/watchdog.php' => config_path('watchdog.php'),
        ], 'watchdog-config');
    }

    /**
     * Load the package migrations.
     */
    private function loadMigrations(): void
    {
        $custom_migrations = config('watchdog.custom_migrations') ?? false;

        if ($custom_migrations) {
            $this->loadMigrationsFrom(database_path('migrations/vendor/junges/watchdog'));
        } else {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations/vendor/junges/watchdog'),
        ], 'watchdog-migrations');
    }

    private function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DeleteExpiredInvitesCommand::class,
            ]);
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('watchdog', Watchdog::class);
    }
}
