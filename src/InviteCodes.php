<?php

namespace Junges\InviteCodes;

use BadMethodCallException;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Junges\InviteCodes\Contracts\InviteCodesContract;
use Junges\InviteCodes\Events\InviteRedeemedEvent;
use Junges\InviteCodes\Exceptions\DuplicateInviteCodeException;
use Junges\InviteCodes\Exceptions\ExpiredInviteCodeException;
use Junges\InviteCodes\Exceptions\InvalidInviteCodeException;
use Junges\InviteCodes\Exceptions\InviteAlreadyRedeemedException;
use Junges\InviteCodes\Exceptions\InviteMustBeAbleToBeRedeemedException;
use Junges\InviteCodes\Exceptions\InviteWithRestrictedUsageException;
use Junges\InviteCodes\Exceptions\SoldOutException;
use Junges\InviteCodes\Exceptions\UserLoggedOutException;
use Junges\InviteCodes\Http\Models\Invite;
use Symfony\Component\HttpFoundation\Response;

class InviteCodes implements InviteCodesContract
{
    protected int $max_usages;
    protected ?string $to = null;
    protected ?CarbonInterface $expires_at;
    protected bool $dispatch_events = true;

    /**
     * @param $name
     * @param $arguments
     * @return InviteCodes
     *
     * @throws BadMethodCallException
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function __call($name, $arguments): self
    {
        if (method_exists($this, $name)) {
            $this->{$name}($arguments);
        } elseif (preg_match('/^canBeUsed(\d+)Times$/', $name, $max_usages)) {
            return $this->maxUsages($max_usages[1]);
        }
        throw new BadMethodCallException('Invalid method called');
    }

    /**
     * If used, no events will be dispatched.
     *
     * @return InviteCodes
     */
    public function withoutEvents(): self
    {
        $this->dispatch_events = false;

        return $this;
    }

    /**
     * @param  string  $code
     * @return Invite
     *
     * @throws ExpiredInviteCodeException
     * @throws InvalidInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     * @throws InviteAlreadyRedeemedException
     */
    public function redeem(string $code): Invite
    {
        $model = app(config('invite-codes.models.invite_model', Invite::class));

        /** @var Invite|null $invite */
        $invite = $model->where('code', Str::upper($code))->first();

        if ($invite === null || ! $this->inviteCanBeRedeemed($invite)) {
            throw new InvalidInviteCodeException('Your invite code is invalid');
        }

        $invite->increment('uses', 1);
        $invite->save();

        if ($this->shouldDispatchEvents()) {
            event(new InviteRedeemedEvent($invite));
        }

        return $invite;
    }

    /**
     * Create a new invite.
     *
     * @return InviteCodes
     */
    public function create(): self
    {
        return $this;
    }

    /**
     * Set the number of allowed redemptions.
     *
     * @param  int  $usages
     * @return InviteCodes
     *
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function maxUsages(int $usages = 1): self
    {
        if ($usages < 1) {
            throw new InviteMustBeAbleToBeRedeemedException();
        }

        $this->max_usages = $usages;

        return $this;
    }

    /**
     * Set the max usages amount to one.
     *
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function canBeUsedOnce(): self
    {
        $this->maxUsages(1);

        return $this;
    }

    /**
     * Set the user who can use this invite.
     *
     * @param  string  $email
     * @return $this
     */
    public function restrictUsageTo(string $email): self
    {
        $this->to = $email;

        return $this;
    }

    /**
     * Set the invite expiration date.
     *
     * @param $date
     * @return InviteCodes
     */
    public function expiresAt($date): self
    {
        if (is_string($date)) {
            $this->expires_at = Carbon::parse($date);
        } elseif ($date instanceof Carbon) {
            $this->expires_at = $date;
        }

        return $this;
    }

    /**
     * Set the expiration date to $days from now.
     *
     * @param  int  $days
     * @return $this
     */
    public function expiresIn(int $days): self
    {
        $expires_at = Carbon::now(config('app.timezone'))->addDays($days)->endOfDay();

        $this->expiresAt($expires_at);

        return $this;
    }

    /**
     * Save the created invite.
     *
     * @return Invite
     */
    public function save(): Invite
    {
        $model = app(config('invite-codes.models.invite_model', Invite::class));

        do {
            $code = Str::upper(Str::random(16));
        } while ($model->where('code', $code)->first() instanceof $model);

        return $model->create([
            'code' => $code,
            'to' => $this->to,
            'uses' => 0,
            'expires_at' => $this->expires_at ?? null,
            'max_usages' => $this->max_usages ?? null,
        ]);
    }

    /**
     * @param  int  $quantity
     * @return Collection
     *
     * @throws DuplicateInviteCodeException
     */
    public function make(int $quantity): Collection
    {
        $invites = collect();

        if (! empty($this->to) && $quantity > 1) {
            DuplicateInviteCodeException::forEmail();
        }

        while ($quantity > 0) {
            $invite = $this->save();
            $invites->push($invite);
            $quantity--;
        }

        return $invites;
    }

    /**
     * @param  Invite  $invite
     * @return bool
     *
     * @throws ExpiredInviteCodeException
     * @throws InviteWithRestrictedUsageException
     * @throws SoldOutException
     * @throws UserLoggedOutException
     * @throws InviteAlreadyRedeemedException
     */
    private function inviteCanBeRedeemed(Invite $invite): bool
    {
        if ($invite->hasRestrictedUsage() && ! Auth::check()) {
            throw new UserLoggedOutException('You must be logged in to use this invite code.', Response::HTTP_FORBIDDEN);
        }

        if ($invite->hasRestrictedUsage() && ! $invite->usageRestrictedToEmail(Auth::user()->{config('invite-codes.user.email_column')})) {
            throw new InviteWithRestrictedUsageException('This invite is not for you.', Response::HTTP_FORBIDDEN);
        }

        if ($invite->hasRestrictedUsage()
            && Auth::check()
            && $invite->usageRestrictedToEmail(Auth::user()->{config('invite-codes.user.email_column')})
            && $invite->isSoldOut()
        ) {
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
    private function shouldDispatchEvents(): bool
    {
        return $this->dispatch_events;
    }
}
