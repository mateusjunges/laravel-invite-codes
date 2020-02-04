<?php

namespace Junges\Watchdog\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Junges\Watchdog\Http\Models\Invite;

class DeletedExpiredInvitesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Invite $invite
     */
    public function __construct()
    {
    }
}
