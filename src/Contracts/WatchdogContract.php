<?php

namespace Junges\Watchdog\Contracts;

use Illuminate\Support\Collection;
use Junges\Watchdog\Exceptions\DuplicateInviteCodeException;
use Junges\Watchdog\Exceptions\ExpiredInviteCodeException;
use Junges\Watchdog\Exceptions\InvalidInviteCodeException;
use Junges\Watchdog\Exceptions\InviteMustBeAbleToBeRedeemedException;
use Junges\Watchdog\Exceptions\InviteWithRestrictedUsageException;
use Junges\Watchdog\Exceptions\SoldOutException;
use Junges\Watchdog\Exceptions\UserLoggedOutException;
use Junges\Watchdog\Http\Models\Invite;
use Junges\Watchdog\Watchdog;

interface WatchdogContract
{

    /**
     * If used, no events will be dispatched.
     * @return Watchdog
     */
    public function withoutEvents() : Watchdog;

    /**
     * @param string $code
     * @return Invite
     * @throws ExpiredInviteCodeException
     * @throws InvalidInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     */
    public function redeem(string $code) : Invite;

    /**
     * Create a new invite.
     * @return Watchdog
     */
    public function create() : Watchdog;

    /**
     * Set the number of allowed redemptions.
     * @param int $usages
     * @return Watchdog
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function maxUsages(int $usages = 1) : Watchdog;

    /**
     * Set the max usages amount to one.
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function canBeUsedOnce() : Watchdog;

    /**
     * Set the user who can use this invite.
     * @param string $email
     * @return Watchdog
     */
    public function restrictUsageTo(string $email) : Watchdog;

    /**
     * Save the created invite.
     * @return Invite
     */
    public function save() : Invite;

    /**
     * @param int $quantity
     * @return \Illuminate\Support\Collection
     * @throws DuplicateInviteCodeException
     */
    public function make(int $quantity) : Collection;
}
