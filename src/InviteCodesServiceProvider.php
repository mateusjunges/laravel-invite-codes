<?php

namespace Junges\InviteCodes;

use Illuminate\Support\ServiceProvider;
use Junges\InviteCodes\Console\Commands\DeleteExpiredInvitesCommand;

class InviteCodesServiceProvider extends ServiceProvider
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
            __DIR__.'/../config/invite-codes.php' => config_path('invite-codes.php'),
        ], 'invite-codes-config');
    }

    /**
     * Load the package migrations.
     */
    private function loadMigrations(): void
    {
        $custom_migrations = config('invite-codes.custom_migrations', false);

        if ($custom_migrations) {
            $this->loadMigrationsFrom(database_path('migrations/vendor/junges/invite-codes'));
        } else {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations/vendor/junges/invite-codes'),
        ], 'invite-codes-migrations');
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
        $this->app->bind('invite_codes', InviteCodes::class);
    }
}
