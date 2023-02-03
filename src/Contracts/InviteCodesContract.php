<?php

namespace Junges\InviteCodes\Contracts;

use Illuminate\Support\Collection;
use Junges\InviteCodes\Exceptions\DuplicateInviteCodeException;
use Junges\InviteCodes\Exceptions\ExpiredInviteCodeException;
use Junges\InviteCodes\Exceptions\InvalidInviteCodeException;
use Junges\InviteCodes\Exceptions\InviteMustBeAbleToBeRedeemedException;
use Junges\InviteCodes\Exceptions\InviteWithRestrictedUsageException;
use Junges\InviteCodes\Exceptions\SoldOutException;
use Junges\InviteCodes\Exceptions\UserLoggedOutException;
use Junges\InviteCodes\Http\Models\Invite;
use Junges\InviteCodes\InviteCodes;

interface InviteCodesContract
{
    /** If used, no events will be dispatched.  */
    public function withoutEvents(): InviteCodes;

    /**
     * @throws ExpiredInviteCodeException
     * @throws InvalidInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     */
    public function redeem(string $code): Invite;

    /** Create a new invite */
    public function create(): InviteCodes;

    /**
     * Set the number of allowed redemptions.
     *
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function maxUsages(int $usages = 1): InviteCodes;

    /** Set the max usages amount to one. */
    public function canBeUsedOnce(): InviteCodes;

    /** Set the user who can use this invite. */
    public function restrictUsageTo(string $email): InviteCodes;

    /** Save the created invite.*/
    public function save(): Invite;

    /** @throws DuplicateInviteCodeException */
    public function make(int $quantity): Collection;
}
