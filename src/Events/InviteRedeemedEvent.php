<?php

namespace Junges\InviteCodes\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Junges\InviteCodes\Http\Models\Invite;

class InviteRedeemedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Invite $invite)
    {
    }
}
