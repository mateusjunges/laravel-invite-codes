<?php

namespace Junges\InviteCodes\Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Junges\InviteCodes\InviteCodesEventServiceProvider;
use Junges\InviteCodes\InviteCodesServiceProvider;
use Junges\InviteCodes\Models\Invite;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        (new InviteCodesServiceProvider($this->app))->boot();
    }

    public function getPackageProviders($app): array
    {
        return [
            InviteCodesServiceProvider::class,
            InviteCodesEventServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('invite-codes.user.email_column', 'email');

        $app['config']->set('views.path', [__DIR__.'/resources/views']);

        // Use test model for users provider
        $app['config']->set('auth.providers.users.model', TestUser::class);
    }

    private function setUpDatabase($app): void
    {
        $app['config']->set('invite-codes.tables.invites_table', 'test_invites_table');

        // Set up models for tests
        $app['config']->set('invite-codes.models.invite_model', Invite::class);

        $this->runMigrations($app);
    }

    private function runMigrations($app): void
    {
        // Include migration files
        $migration = require __DIR__.'/../database/migrations/2020_01_29_162459_create_invites_table.php';

        $migration->up();

        $app['db']->connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
