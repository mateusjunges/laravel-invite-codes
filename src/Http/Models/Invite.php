<?php

namespace Junges\InviteCodes\Http\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Junges\InviteCodes\Contracts\InviteContract;
use Junges\InviteCodes\Events\InviteCreatedEvent;

/**
 * Class Invite.
 *
 * @method static Builder usedOnce() All invites used once.
 * @method static Builder neverUsed() All never used invites.
 * @method static Builder mostUsed() The most used invite.
 * @method static Builder expired() All expired invites.
 * @method static Builder soldOut() All sold out invites.
 */
class Invite extends Model implements InviteContract
{
    protected $table;

    protected $dates = ['deleted_at', 'expires_at'];

    protected $fillable = [
        'code',
        'uses',
        'max_usages',
        'to',
        'expires_at',
    ];

    protected $dispatchesEvents = [
        'creating' => InviteCreatedEvent::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('invite-codes.tables.invites_table'));
    }

    /**
     * Sets 'code' as primary key for Route Model Bindings.
     */
    public function getRouteKeyName()
    {
        return 'code';
    }

    /**
     * Check if an invite code can be used redeemed.
     *
     * @return bool
     */
    public function canBeRedeemed(): bool
    {
        return ! $this->isExpired() && ! $this->isSoldOut() && ! $this->hasRestrictedUsage();
    }

    /**
     * Check if an invite is to the user who has the specified email.
     *
     * @param $email
     * @return bool
     */
    public function usageRestrictedToEmail($email): bool
    {
        return $this->to === $email;
    }

    /**
     * Check if an invite is usable for only one person.
     *
     * @return bool
     */
    public function hasRestrictedUsage(): bool
    {
        return $this->to !== null;
    }

    /**
     * Checks if the invite code is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (empty($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the invite code has been sold out.
     *
     * @return bool
     */
    public function isSoldOut(): bool
    {
        if ($this->max_usages === null) {
            return false;
        }

        return (int) $this->uses >= $this->max_usages;
    }

    /**
     * Invites used once.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeUsedOnce(Builder $query): Builder
    {
        return $query->where('uses', '=', 1);
    }

    /**
     * Invites never used.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNeverUsed(Builder $query): Builder
    {
        return $query->where('uses', '=', 0);
    }

    /**
     * Most used invite code.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeMostUsed(Builder $query): Builder
    {
        return $query->orderBy('uses', 'desc')->limit(1);
    }

    /**
     * Expired invites.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', Carbon::now(config('app.timezone')));
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSoldOut(Builder $query): Builder
    {
        return $query->whereNotNull('max_usages')
            ->whereColumn('uses', '=', 'max_usages');
    }
}
