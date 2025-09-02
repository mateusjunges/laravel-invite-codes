<?php declare(strict_types=1);

namespace Junges\InviteCodes\Tests\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Junges\InviteCodes\Events\DeletedExpiredInvitesEvent;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Models\Invite;
use Junges\InviteCodes\Tests\TestCase;

class DeleteExpiredInvitesCommandTest extends TestCase
{
    public function test_it_can_delete_expired_events()
    {
        InviteCodes::create()->expiresAt(\Carbon\Carbon::now()->addDay())->make(10);

        InviteCodes::create()->expiresAt(\Carbon\Carbon::now()->subWeek())->make(5);

        $this->assertCount(15, Invite::all());

        Artisan::call('invite-codes:clear');

        $this->assertCount(10, Invite::all());
    }

    public function test_it_dispatch_an_event_when_expired_invites_are_deleted()
    {
        Event::fake();

        InviteCodes::create()->expiresAt(\Carbon\Carbon::now()->addDay())->make(10);

        InviteCodes::create()->expiresAt(\Carbon\Carbon::now()->subWeek())->make(5);

        $this->assertCount(15, Invite::all());

        Artisan::call('invite-codes:clear');

        $this->assertCount(10, Invite::all());

        Event::assertDispatched(DeletedExpiredInvitesEvent::class);
    }
}
