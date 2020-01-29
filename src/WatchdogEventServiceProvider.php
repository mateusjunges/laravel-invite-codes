<?php

namespace Junges\Watchdog;

use App\Listeners\InviteCreatedEventListener;
use Illuminate\Support\ServiceProvider;
use Junges\Watchdog\Events\InviteCreatedEvent;

class WatchdogEventServiceProvider extends ServiceProvider
{

    public array $listen = array(
        InviteCreatedEvent::class => array(
            InviteCreatedEventListener::class,
        ),
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
