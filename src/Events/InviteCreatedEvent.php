<?php

namespace Junges\Watchdog\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Junges\Watchdog\Http\Models\Invite;

class InviteCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Invite $invite;

    /**
     * Create a new event instance.
     *
     * @param Invite $invite
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }
}
