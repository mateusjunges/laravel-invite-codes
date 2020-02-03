<?php

namespace Junges\Watchdog\Tests;

use Carbon\Carbon;
use Junges\Watchdog\Facades\Watchdog;
use Junges\Watchdog\Http\Models\Invite;

class InviteModelMethodsTest extends TestCase
{
    public function test_is_sold_out_method()
    {
        $invite = Watchdog::create()
            ->canBeUsedOnce()
            ->save();

        $invite = Watchdog::redeem($invite->code);

        $this->assertTrue($invite->isSoldOut());
    }

    public function test_usage_restricted_to_email_method()
    {
        $invite = Watchdog::create()
            ->restrictUsageTo('contato@mateusjunges.com')
            ->save();

        $this->assertTrue($invite->hasRestrictedUsage());

        $this->assertTrue($invite->usageRestrictedToEmail('contato@mateusjunges.com'));
    }

    public function test_has_restrict_usage_method()
    {
        $invite = Watchdog::create()
            ->restrictUsageTo('contato@mateusjunges.com')
            ->save();

        $this->assertTrue($invite->hasRestrictedUsage());
    }

    public function test_is_expired_method()
    {
        $invite = Watchdog::create()
            ->expiresAt(Carbon::now()->subDay())
            ->canBeUsedOnce()
            ->save();

        $this->assertTrue($invite->isExpired());
    }

    public function test_used_once_scope()
    {
        $invites = Watchdog::create()
            ->make(10);

        $invite = Watchdog::redeem($invites->first()->code);

        $this->assertCount(1, Invite::usedOnce()->get());
    }

    public function test_expired_scope()
    {
        Watchdog::create()->make(10);
        Watchdog::create()->expiresAt(Carbon::now()->subDay())->make(10);

        $this->assertCount(10, Invite::expired()->get());
    }

    public function test_sold_out_scope()
    {
        Watchdog::make(10);

        $invite = Watchdog::create()
            ->canBeUsedOnce()
            ->save();

        Watchdog::redeem($invite->code);

        $this->assertCount(1, Invite::soldOut()->get());
    }
}
