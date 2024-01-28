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
use Junges\InviteCodes\Factory;
use Junges\InviteCodes\Models\Invite;

interface InviteCodesFactory
{
    /** If used, no events will be dispatched.  */
    public function withoutEvents(): Factory;

    /**
     * @throws ExpiredInviteCodeException
     * @throws InvalidInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     */
    public function redeem(string $code): Invite;

    /** Create a new invite */
    public function create(): Factory;

    /**
     * Set the number of allowed redemptions.
     *
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function maxUsages(int $usages = 1): Factory;

    /** Set the max usages amount to one. */
    public function canBeUsedOnce(): Factory;

    /** Set the user who can use this invite. */
    public function restrictUsageTo(string $email): Factory;

    /** Save the created invite.*/
    public function save(): Invite;

    /** @throws DuplicateInviteCodeException */
    public function make(int $quantity): Collection;
}
