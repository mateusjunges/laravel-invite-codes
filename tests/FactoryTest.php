<?php

namespace Junges\InviteCodes\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Junges\InviteCodes\Contracts\InviteContract;
use Junges\InviteCodes\Events\InviteRedeemedEvent;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Models\Invite;

class FactoryTest extends TestCase
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

    public function test_macro(): void
    {
        InviteCodes::macro('restrictedTo', function (string $email) {
            return $this->restrictUsageTo($email)->save();
        });

        $invite = InviteCodes::restrictedTo('test@example.com');

        $this->assertInstanceOf(InviteContract::class, $invite);
        $this->assertTrue($invite->usageRestrictedToEmail('test@example.com'));
    }

    public function test_it_can_run_quietly(): void
    {
        Event::fake();

        Invite::query()->create([
            'code' => $code = Str::random(),
        ]);

        InviteCodes::quietly(static function () use ($code) {
            InviteCodes::redeem($code);
        });

        Event::assertNotDispatched(InviteRedeemedEvent::class);
    }

    public function test_it_can_customize_how_invite_code_is_created(): void
    {
        InviteCodes::createInviteCodeUsing(static function () {
            return 'PREFIX-12345';
        });

        $invite = InviteCodes::create()->save();

        $this->assertSame('PREFIX-12345', $invite->code);
    }
}
