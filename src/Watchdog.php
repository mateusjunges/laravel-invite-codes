<?php

namespace Junges\Watchdog;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Junges\Watchdog\Contracts\WatchdogContract;
use Junges\Watchdog\Events\InviteRedeemedEvent;
use Junges\Watchdog\Exceptions\DuplicateInviteCodeException;
use Junges\Watchdog\Exceptions\ExpiredInviteCodeException;
use Junges\Watchdog\Exceptions\InvalidInviteCodeException;
use Junges\Watchdog\Exceptions\InviteAlreadyRedeemedException;
use Junges\Watchdog\Exceptions\InviteWithRestrictedUsageException;
use Junges\Watchdog\Exceptions\InviteMustBeAbleToBeRedeemedException;
use Junges\Watchdog\Exceptions\SoldOutException;
use Junges\Watchdog\Exceptions\UserLoggedOutException;
use Junges\Watchdog\Http\Models\Invite;
use Symfony\Component\HttpFoundation\Response;

class Watchdog implements WatchdogContract
{
    protected int $max_usages;
    protected $to = null;
    protected $expires_at = null;
    protected $dispatch_events = true;

    /**
     * @param $name
     * @param $arguments
     * @return Watchdog
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function __call($name, $arguments) : Watchdog
    {
        if (method_exists($this, $name)) {
            $this->{$name}($arguments);
        } else {
            if (preg_match("/canBeUsed[0-9]*Times/", $name)){
                preg_match("/\d+/", $name, $max_usages);
               return $this->maxUsages($max_usages[0]);
            }
        }
    }

    /**
     * If used, no events will be dispatched.
     * @return Watchdog
     */
    public function withoutEvents() : Watchdog
    {
        $this->dispatch_events = false;

        return $this;
    }

    /**
     * @param string $code
     * @return bool
     * @throws ExpiredInviteCodeException
     * @throws InvalidInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     * @throws InviteAlreadyRedeemedException
     */
    public function redeem(string $code) : bool
    {
        try {
            $model = app(config('watchdog.models.invite_model'));
            $invite = $model->where('code', Str::upper($code))->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new InvalidInviteCodeException('Your invite code is invalid');
        }

        if ($this->inviteCanBeRedeemed($invite)) {
            /*** @var Invite $invite */
            $invite->increment('uses', 1);
            $invite->save();
            if ($this->shouldDispatchEvents()) {
                event(new InviteRedeemedEvent($invite));
            }

            return true;
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
     * @param int $usages
     * @return Watchdog
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function maxUsages(int $usages  = 1) : Watchdog
    {
        if ($usages < 1) {
            throw new InviteMustBeAbleToBeRedeemedException();
        } else {
            $this->max_usages = $usages;
        }

        return $this;
    }

    /**
     * Set the max usages amount to one.
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function canBeUsedOnce() : Watchdog
    {
        $this->maxUsages(1);

        return $this;
    }

    /**
     * Set the user who can use this invite.
     * @param string $email
     * @return $this
     */
    public function restrictUsageTo(string $email) : Watchdog
    {
        $this->to = $email;
        return $this;
    }

    /**
     * Set the invite expiration date.
     * @param $date
     * @return Watchdog
     */
    public function expiresAt($date) : Watchdog
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
    public function expiresIn(int $days) : Watchdog
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
        $model = app(config('watchdog.models.invite_model'));

        return $model->create([
            'code' => Str::upper(Str::random(16)),
            'to' => $this->to,
            'uses' => 0,
            'expires_at' => $this->expires_at ?? null,
            'max_usages' => $this->max_usages ?? null,
        ]);
    }

    /**
     * @param int $quantity
     * @return \Illuminate\Support\Collection
     * @throws DuplicateInviteCodeException
     */
    public function make(int $quantity) : Collection
    {
        $invites = collect();

        if (! empty($this->to) and $quantity > 1) {
            throw DuplicateInviteCodeException::forEmail();
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
     * @throws ExpiredInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     * @throws InviteAlreadyRedeemedException
     */
    private function inviteCanBeRedeemed(Invite $invite, string $email = null) : bool
    {
        if ($invite->hasRestrictedUsage() and ! Auth::check()) {
            throw new UserLoggedOutException('You must be logged in to use this invite code.', Response::HTTP_FORBIDDEN);
        }

        if ($invite->hasRestrictedUsage() and ! $invite->usageRestrictedToEmail(Auth::user()->{config('watchdog.user.email_column')})) {
            throw new InviteWithRestrictedUsageException('This invite is not for you.', Response::HTTP_FORBIDDEN);
        }

        if ($invite->hasRestrictedUsage()
            and Auth::check()
            and $invite->usageRestrictedToEmail(Auth::user()->{config('watchdog.user.email_column')})
            and $invite->isSoldOut()) {
            throw new InviteAlreadyRedeemedException('This invite has already been redeemed', Response::HTTP_FORBIDDEN);
        }

        if ($invite->isSoldOut()) {
            throw new SoldOutException('This invite can\'t be used anymore', Response::HTTP_FORBIDDEN);
        }

        if ($invite->isExpired()) {
            throw new ExpiredInviteCodeException('This invite has been expired.', Response::HTTP_FORBIDDEN);
        }
        return true;
    }

    /**
     * @return bool
     */
    private function shouldDispatchEvents() : bool
    {
        return $this->dispatch_events;
    }
}
