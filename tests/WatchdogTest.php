<?php

namespace Junges\Watchdog\Tests;

use Illuminate\Support\Facades\Event;
use Junges\Watchdog\Events\InviteRedeemedEvent;
use Junges\Watchdog\Facades\Watchdog;


class WatchdogTest extends TestCase
{
    public function test_an_event_is_dispatched_when_invite_has_been_redeemed()
    {
        Event::fake();

        $invite = Watchdog::create()->save();

        Watchdog::redeem($invite->code);

        Event::assertDispatched(InviteRedeemedEvent::class, fn($event) => $event->invite->code === $invite->code);
    }

    public function test_a_user_can_redeem_invite_codes_without_dispatching_events()
    {
        Event::fake();

        $invite = Watchdog::create()->save();

        Watchdog::withoutEvents()->redeem($invite->code);

        Event::assertNotDispatched(InviteRedeemedEvent::class);
    }
}
