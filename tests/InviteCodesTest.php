<?php

namespace Junges\InviteCodes\Tests;

use Illuminate\Support\Facades\Event;
use Junges\InviteCodes\Events\InviteRedeemedEvent;
use Junges\InviteCodes\Facades\InviteCodes;

class InviteCodesTest extends TestCase
{
    public function test_an_event_is_dispatched_when_invite_has_been_redeemed()
    {
        Event::fake();

        $invite = InviteCodes::create()->save();

        InviteCodes::redeem($invite->code);

        Event::assertDispatched(InviteRedeemedEvent::class, fn ($event) => $event->invite->code === $invite->code);
    }

    public function test_a_user_can_redeem_invite_codes_without_dispatching_events()
    {
        Event::fake();

        $invite = InviteCodes::create()->save();

        InviteCodes::withoutEvents()->redeem($invite->code);

        Event::assertNotDispatched(InviteRedeemedEvent::class);
    }
}
