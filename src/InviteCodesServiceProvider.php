<?php declare(strict_types=1);

namespace Junges\InviteCodes;

use Illuminate\Support\ServiceProvider;
use Junges\InviteCodes\Console\Commands\DeleteExpiredInvitesCommand;
use Junges\InviteCodes\Contracts\InviteCodesFactory;

final class InviteCodesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishesConfig();
        $this->loadMigrations();
        $this->loadCommands();
    }

    /** Register any application services. */
    public function register(): void
    {
        $this->app->bind(InviteCodesFactory::class, Factory::class);
    }

    /** Load and publishes the package configuration file. */
    private function publishesConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/invite-codes.php' => config_path('invite-codes.php'),
        ], 'invite-codes-config');
    }

    /** Load the package migrations. */
    private function loadMigrations(): void
    {
        $custom_migrations = config('invite-codes.custom_migrations', false);

        if ($custom_migrations) {
            $this->loadMigrationsFrom(database_path('migrations/vendor/junges/invite-codes'));
        } else {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'invite-codes-migrations');
    }

    private function loadCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DeleteExpiredInvitesCommand::class,
            ]);
        }
    }
}
