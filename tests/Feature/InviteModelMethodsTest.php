<?php declare(strict_types=1);

use Carbon\Carbon;
use Junges\InviteCodes\Exceptions\DuplicateInviteCodeException;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Models\Invite;

it('has is_sold_out method', function () {
    $invite = InviteCodes::create()
        ->canBeUsedOnce()
        ->save();

    $invite = InviteCodes::redeem($invite->code);

    expect($invite->isSoldOut())->toBeTrue();
});

it('has usage_restricted_to_email method', function () {
    $invite = InviteCodes::create()
        ->restrictUsageTo('contato@mateusjunges.com')
        ->save();

    expect($invite->hasRestrictedUsage())->toBeTrue();
    expect($invite->usageRestrictedToEmail('contato@mateusjunges.com'))->toBeTrue();
});

it('has has_restrict_usage method', function () {
    $invite = InviteCodes::create()
        ->restrictUsageTo('contato@mateusjunges.com')
        ->save();

    expect($invite->hasRestrictedUsage())->toBeTrue();
});

it('throws duplicate invite code exception', function () {
    InviteCodes::restrictUsageTo('contato@mateusjunges.com')->make(2);
})->throws(DuplicateInviteCodeException::class);

it('has is_expired method', function () {
    $invite = InviteCodes::create()
        ->expiresAt(Carbon::now()->subDay())
        ->canBeUsedOnce()
        ->save();

    expect($invite->isExpired())->toBeTrue();
});

it('has used_once scope', function () {
    $invites = InviteCodes::create()
        ->make(10);

    InviteCodes::redeem($invites->first()->code);

    expect(Invite::usedOnce()->get())->toHaveCount(1);
});

it('has expired scope', function () {
    InviteCodes::create()->make(10);
    InviteCodes::create()->expiresAt(Carbon::now()->subDay())->make(10);

    expect(Invite::expired()->get())->toHaveCount(10);
});

it('has sold_out scope', function () {
    InviteCodes::make(10);

    $invite = InviteCodes::create()
        ->canBeUsedOnce()
        ->save();

    InviteCodes::redeem($invite->code);

    expect(Invite::soldOut()->get())->toHaveCount(1);
});
