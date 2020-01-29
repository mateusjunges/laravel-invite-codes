<?php

namespace Junges\Watchdog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Junges\Watchdog\Contracts\WatchdogContract;
use Junges\Watchdog\Exceptions\ExpiredInviteException;
use Junges\Watchdog\Exceptions\InvalidInviteException;
use Junges\Watchdog\Exceptions\InviteForAnotherPersonException;
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
    public function redeem($code) : Watchdog
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
     * @param string|null $code
     * @return Watchdog
     */
    public function create(string $code = null) : Watchdog
    {
        $this->code = $code ?? Str::random(16);
        return $this;
    }

    /**
     * Set the number of allowed redemptions.
     * @param int|null $number
     * @return Watchdog
     */
    public function allowRedemption(int $number = null) : Watchdog
    {
        if (is_null($number)) {
            $this->allowed_redemptions = 1;
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
     * Save the created invite.
     * @return Invite
     */
    public function save() : Invite
    {
        $invite = Invite::create([
            'code' => $this->code,
            'to' => $this->to,
            'expires_at' => $this->expires_at,
            'max_usages' => $this->allowed_redemptions,
        ]);

        return $invite;
    }

    /**
     * @param Invite $invite
     * @param string|null $email
     * @return bool
     * @throws ExpiredInviteException
     * @throws InviteForAnotherPersonException
     * @throws SoldOutException
     */
    public function inviteCanBeRedeemed(Invite $invite, string $email = null)
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
