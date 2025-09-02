<?php declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Junges\InviteCodes\Events\DeletedExpiredInvitesEvent;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Models\Invite;

it('can delete expired events', function () {
    InviteCodes::create()->expiresAt(Carbon\Carbon::now()->addDay())->make(10);

    InviteCodes::create()->expiresAt(Carbon\Carbon::now()->subWeek())->make(5);

    expect(Invite::all())->toHaveCount(15);

    Artisan::call('invite-codes:clear');

    expect(Invite::all())->toHaveCount(10);
});

it('dispatches an event when expired invites are deleted', function () {
    Event::fake();

    InviteCodes::create()->expiresAt(Carbon\Carbon::now()->addDay())->make(10);

    InviteCodes::create()->expiresAt(Carbon\Carbon::now()->subWeek())->make(5);

    expect(Invite::all())->toHaveCount(15);

    Artisan::call('invite-codes:clear');

    expect(Invite::all())->toHaveCount(10);

    Event::assertDispatched(DeletedExpiredInvitesEvent::class);
});
