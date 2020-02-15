<?php

namespace Junges\InviteCodes;

use Illuminate\Support\ServiceProvider;
use Junges\InviteCodes\Events\InviteCreatedEvent;
use Junges\InviteCodes\Events\InviteRedeemedEvent;

class InviteCodesEventServiceProvider extends ServiceProvider
{
    public array $listen = [
        InviteCreatedEvent::class => [

        ],
        InviteRedeemedEvent::class => [

        ],
    ];

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
