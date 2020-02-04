<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Junges\Watchdog\Events\DeletedExpiredInvitesEvent;
use Junges\Watchdog\Facades\Watchdog;
use Junges\Watchdog\Http\Models\Invite;
use Junges\Watchdog\Tests\TestCase;

class DeleteExpiredInvitesCommandTest extends TestCase
{
    public function test_it_can_delete_expired_events()
    {
        Watchdog::create()->expiresAt(\Carbon\Carbon::now()->addDay())->make(10);

        Watchdog::create()->expiresAt(\Carbon\Carbon::now()->subWeek())->make(5);

        $this->assertCount(15, Invite::all());

        Artisan::call('watchdog:clear');

        $this->assertCount(10, Invite::all());
    }

    public function test_it_dispatch_an_event_when_expired_invites_are_deleted()
    {
        Event::fake();

        Watchdog::create()->expiresAt(\Carbon\Carbon::now()->addDay())->make(10);

        Watchdog::create()->expiresAt(\Carbon\Carbon::now()->subWeek())->make(5);

        $this->assertCount(15, Invite::all());

        Artisan::call('watchdog:clear');

        $this->assertCount(10, Invite::all());

        Event::assertDispatched(DeletedExpiredInvitesEvent::class);
    }
}
