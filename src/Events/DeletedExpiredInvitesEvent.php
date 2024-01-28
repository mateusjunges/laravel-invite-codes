<?php

namespace Junges\InviteCodes\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeletedExpiredInvitesEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
}
