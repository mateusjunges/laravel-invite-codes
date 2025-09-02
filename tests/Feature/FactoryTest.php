<?php declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Junges\InviteCodes\Contracts\InviteContract;
use Junges\InviteCodes\Events\InviteRedeemedEvent;
use Junges\InviteCodes\Facades\InviteCodes;

it('dispatches an event when invite has been redeemed', function () {
    Event::fake();

    $invite = InviteCodes::create()->save();

    InviteCodes::redeem($invite->code);

    Event::assertDispatched(InviteRedeemedEvent::class, fn ($event) => $event->invite->code === $invite->code);
});

it('can redeem invite codes without dispatching events', function () {
    Event::fake();

    $invite = InviteCodes::create()->save();

    InviteCodes::withoutEvents()->redeem($invite->code);

    Event::assertNotDispatched(InviteRedeemedEvent::class);
});

it('supports macros', function () {
    InviteCodes::macro('restrictedTo', function (string $email) {
        return $this->restrictUsageTo($email)->save();
    });

    $invite = InviteCodes::restrictedTo('test@example.com');

    expect($invite)->toBeInstanceOf(InviteContract::class);
    expect($invite->usageRestrictedToEmail('test@example.com'))->toBeTrue();
});

it('can customize how invite code is created', function () {
    InviteCodes::createInviteCodeUsing(static function () {
        return 'PREFIX-12345';
    });

    $invite = InviteCodes::create()->save();

    expect($invite->code)->toBe('PREFIX-12345');

    InviteCodes::createInviteCodeUsing(null);
});
