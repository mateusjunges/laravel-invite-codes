<?php

namespace Junges\Watchdog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Junges\Watchdog\Contracts\WatchdogContract;
use Junges\Watchdog\Exceptions\DuplicateInviteException;
use Junges\Watchdog\Exceptions\ExpiredInviteException;
use Junges\Watchdog\Exceptions\InvalidInviteException;
use Junges\Watchdog\Exceptions\InviteForAnotherPersonException;
use Junges\Watchdog\Exceptions\InviteMustBeAbleToBeRedeemedException;
use Junges\Watchdog\Exceptions\SoldOutException;
use Junges\Watchdog\Http\Models\Invite;

class Watchdog
{
    private int $allowed_redemptions;
    private string $to;
    private Carbon $expires_at;
    private string $code;

    /**
     * @param $code
     * @return Watchdog|void
     * @throws ExpiredInviteException
     * @throws InvalidInviteException
     * @throws InviteForAnotherPersonException
     * @throws SoldOutException
     */
    public function redeem(string $code) : Watchdog
    {
        try {
            $invite = Invite::where('code', Str::upper($code))->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new InvalidInviteException('Your invite code is invalid');
        }

        if ($this->inviteCanBeRedeemed($invite)) {
            $invite->increment('uses');
            return $this;
        }
    }

    /**
     * Create a new invite.
     * @return Watchdog
     */
    public function create() : Watchdog
    {
        return $this;
    }

    /**
     * Set the number of allowed redemptions.
     * @param int $number
     * @return Watchdog
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function maxUsages(int $number  = 1) : Watchdog
    {
        if ($number < 1) {
            throw new InviteMustBeAbleToBeRedeemedException();
        } else {
            $this->allowed_redemptions = $number;
        }

        return $this;
    }

    /**
     * Set the user who can use this invite.
     * @param string $email
     * @return $this
     */
    public function to(string $email)
    {
        $this->to = $email;
        return $this;
    }

    /**
     * Set the invite expiration date.
     */
    public function expiresAt($date)
    {
        if (is_string($date)) {
            $this->expires_at = Carbon::parse($date);
        }else if ($date instanceof Carbon) {
            $this->expires_at = $date;
        }

        return $this;
    }

    /**
     * Set the expiration date to $days from now.
     * @param int $days
     * @return $this
     */
    public function expiresIn(int $days)
    {
        $expires_at = Carbon::now(config('app.timezone'))->addDays($days)->endOfDay();

        $this->expiresAt($expires_at);

        return $this;
    }

    /**
     * Save the created invite.
     * @return Invite
     */
    public function save() : Invite
    {
        $invite = Invite::create([
            'code' => Str::upper(Str::random(16)),
            'to' => $this->to,
            'expires_at' => $this->expires_at,
            'max_usages' => $this->allowed_redemptions,
        ]);

        return $invite;
    }

    /**
     * @param int $quantity
     * @return \Illuminate\Support\Collection
     * @throws DuplicateInviteException
     */
    public function make(int $quantity) : Collection
    {
        $invites = collect();

        if (! is_null($quantity) and $quantity > 1) {
            throw DuplicateInviteException::forEmail();
        }

        while ($quantity > 0) {
            $invite = $this->save();
            $invites->push($invite);
            $quantity--;
        }

        return $invites;
    }

    /**
     * @param Invite $invite
     * @param string|null $email
     * @return bool
     * @throws ExpiredInviteException
     * @throws InviteForAnotherPersonException
     * @throws SoldOutException
     */
    private function inviteCanBeRedeemed(Invite $invite, string $email = null)
    {
        if ($invite->isForSpecificUser() and $invite->createdFor($email)) {
            throw new InviteForAnotherPersonException('This invite is not for you.');
        }
        if ($invite->isSoldOut()) {
            throw new SoldOutException('This invite can\'t be used anymore');
        }
        if ($invite->isExpired()) {
            throw new ExpiredInviteException('This invite has been expired.');
        }
        return true;
    }
}
