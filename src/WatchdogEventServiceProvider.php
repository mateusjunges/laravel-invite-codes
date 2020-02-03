<?php

namespace Junges\Watchdog;

use Illuminate\Support\ServiceProvider;
use Junges\Watchdog\Events\InviteCreatedEvent;
use Junges\Watchdog\Events\InviteRedeemedEvent;

class WatchdogEventServiceProvider extends ServiceProvider
{

    public array $listen = array(
        InviteCreatedEvent::class => [

        ],
        InviteRedeemedEvent::class => [

        ]
    );

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
