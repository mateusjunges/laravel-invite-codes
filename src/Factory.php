<?php

namespace Junges\InviteCodes;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Junges\InviteCodes\Contracts\InviteCodesFactory;
use Junges\InviteCodes\Contracts\InviteContract;
use Junges\InviteCodes\Events\InviteRedeemedEvent;
use Junges\InviteCodes\Exceptions\DuplicateInviteCodeException;
use Junges\InviteCodes\Exceptions\ExpiredInviteCodeException;
use Junges\InviteCodes\Exceptions\InvalidInviteCodeException;
use Junges\InviteCodes\Exceptions\InviteAlreadyRedeemedException;
use Junges\InviteCodes\Exceptions\InviteMustBeAbleToBeRedeemedException;
use Junges\InviteCodes\Exceptions\InviteWithRestrictedUsageException;
use Junges\InviteCodes\Exceptions\SoldOutException;
use Junges\InviteCodes\Exceptions\UserLoggedOutException;
use Junges\InviteCodes\Models\Invite;
use Symfony\Component\HttpFoundation\Response;

class Factory implements InviteCodesFactory
{
    use Macroable;
    use Conditionable;

    protected int $max_usages;
    protected ?string $restrictedTo = null;
    protected ?CarbonInterface $expires_at;
    protected bool $dispatch_events = true;
    protected static ?\Closure $createInviteCodeUsing = null;

    public static function createInviteCodeUsing(callable $callable = null): void
    {
        self::$createInviteCodeUsing = $callable !== null ? $callable(...) : null;
    }

    /** If used, no events will be dispatched. */
    public function withoutEvents(): self
    {
        $this->dispatch_events = false;

        return $this;
    }

    /**
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
        $invite = $model->where('code', $code)->first();

        if (! $invite instanceof InviteContract || ! $this->inviteCanBeRedeemed($invite)) {
            throw new InvalidInviteCodeException('Your invite code is invalid');
        }

        $invite->update(['uses' => $invite->uses + 1]);

        if ($this->shouldDispatchEvents()) {
            event(new InviteRedeemedEvent($invite));
        }

        return $invite;
    }

    /** Create a new invite. */
    public function create(): self
    {
        return $this;
    }

    /**
     * Set the number of allowed redemptions.
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
     * @inheritdoc
     *
     * @throws InviteMustBeAbleToBeRedeemedException
     */
    public function canBeUsedOnce(): self
    {
        $this->maxUsages(1);

        return $this;
    }

    /** @inheritdoc . */
    public function restrictUsageTo(string $email): self
    {
        $this->restrictedTo = $email;

        return $this;
    }

    /** Set the invite expiration date. */
    public function expiresAt(CarbonInterface|string $date): self
    {
        $this->expires_at = match(true) {
            is_string($date) => Carbon::parse($date),
            $date instanceof CarbonInterface => $date,
        };

        return $this;
    }

    /** Set the expiration date to $days from now. */
    public function expiresIn(int $days): self
    {
        $expires_at = Carbon::now(config('app.timezone'))->addDays($days)->endOfDay();

        $this->expiresAt($expires_at);

        return $this;
    }

    /** @inheritdoc */
    public function save(): Invite
    {
        $model = app(config('invite-codes.models.invite_model', Invite::class));

        do {
            $code = $this->createInvitationCode();
        } while ($model->where('code', $code)->first() instanceof $model);

        return $model->create([
            'code' => $code,
            'to' => $this->restrictedTo,
            'uses' => 0,
            'expires_at' => $this->expires_at ?? null,
            'max_usages' => $this->max_usages ?? null,
        ]);
    }

    /** @throws DuplicateInviteCodeException */
    public function make(int $quantity): Collection
    {
        $invites = collect();

        if (! empty($this->restrictedTo) && $quantity > 1) {
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

        if ($invite->hasRestrictedUsage() && ! $invite->usageRestrictedToEmail(Auth::user()->{config('invite-codes.user.email_column', 'email')})) {
            throw new InviteWithRestrictedUsageException('This invite is not for you.', Response::HTTP_FORBIDDEN);
        }

        if ($invite->hasRestrictedUsage()
            && Auth::check()
            && $invite->usageRestrictedToEmail(Auth::user()->{config('invite-codes.user.email_column', 'email')})
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

    private function shouldDispatchEvents(): bool
    {
        return $this->dispatch_events;
    }

    private function createInvitationCode(): string
    {
        if (self::$createInviteCodeUsing instanceof \Closure) {
            return call_user_func(self::$createInviteCodeUsing);
        }

        return Str::upper(Str::random(16));
    }
}
