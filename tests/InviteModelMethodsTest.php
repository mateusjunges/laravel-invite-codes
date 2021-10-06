<?php

namespace Junges\InviteCodes\Tests;

use Carbon\Carbon;
use Junges\InviteCodes\Exceptions\DuplicateInviteCodeException;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Http\Models\Invite;

class InviteModelMethodsTest extends TestCase
{
    public function test_is_sold_out_method()
    {
        $invite = InviteCodes::create()
            ->canBeUsedOnce()
            ->save();

        $invite = InviteCodes::redeem($invite->code);

        $this->assertTrue($invite->isSoldOut());
    }

    public function test_usage_restricted_to_email_method()
    {
        $invite = InviteCodes::create()
            ->restrictUsageTo('contato@mateusjunges.com')
            ->save();

        $this->assertTrue($invite->hasRestrictedUsage());

        $this->assertTrue($invite->usageRestrictedToEmail('contato@mateusjunges.com'));
    }

    public function test_has_restrict_usage_method()
    {
        $invite = InviteCodes::create()
            ->restrictUsageTo('contato@mateusjunges.com')
            ->save();

        $this->assertTrue($invite->hasRestrictedUsage());
    }

    public function test_duplicate_invite_code_exception()
    {
        $this->expectException(DuplicateInviteCodeException::class);
        InviteCodes::restrictUsageTo('contato@mateusjunges.com')->make(2);
    }

    public function test_is_expired_method()
    {
        $invite = InviteCodes::create()
            ->expiresAt(Carbon::now()->subDay())
            ->canBeUsedOnce()
            ->save();

        $this->assertTrue($invite->isExpired());
    }

    public function test_used_once_scope()
    {
        $invites = InviteCodes::create()
            ->make(10);

        $invite = InviteCodes::redeem($invites->first()->code);

        $this->assertCount(1, Invite::usedOnce()->get());
    }

    public function test_expired_scope()
    {
        InviteCodes::create()->make(10);
        InviteCodes::create()->expiresAt(Carbon::now()->subDay())->make(10);

        $this->assertCount(10, Invite::expired()->get());
    }

    public function test_sold_out_scope()
    {
        InviteCodes::make(10);

        $invite = InviteCodes::create()
            ->canBeUsedOnce()
            ->save();

        InviteCodes::redeem($invite->code);

        $this->assertCount(1, Invite::soldOut()->get());
    }
}
