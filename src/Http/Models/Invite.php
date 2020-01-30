<?php

namespace Junges\Watchdog\Http\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Junges\Watchdog\Events\InviteCreatedEvent;

/**
 * Class Invite
 * @method static Builder usedOnce() All invites used once.
 * @method static Builder neverUsed() All never used invites.
 * @method static Builder mostUsed() The most used invite.
 * @method static Builder expired() All expired invites.
 * @method static Builder soldOut() All sold out invites.
 */
class Invite extends Model
{
    protected $table;

    protected $dates = array('deleted_at', 'expires_at');

    protected $fillable = array(
        'code',
        'uses',
        'max_usages',
        'to',
        'expires_at'
    );

    protected $dispatchesEvents = array(
        'creating' => InviteCreatedEvent::class,
    );

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('watchdog.tables.invites_table'));
    }

    /**
     * Check if an invite code can be used redeemed.
     * @return bool
     */
    public function canBeRedeemed()
    {
        return ! $this->isExpired() and ! $this->isSoldOut() and ! $this->hasRestrictedUsage();
    }

    /**
     * Check if an invite is to the user who has the specified email.
     * @param $email
     * @return bool
     */
    public function usageRestrictedToEmail($email)
    {
        return $this->to === $email;
    }

    /**
     * Check if an invite is usable for only one person.
     * @return bool
     */
    public function hasRestrictedUsage()
    {
        return ! is_null($this->to);
    }

    /**
     * Checks if the invite code is expired.
     * @return bool
     */
    public function isExpired() : bool
    {
        if (empty($this->expires_at)) {
            return true;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invite code has been sold out.
     * @return bool
     */
    public function isSoldOut() : bool
    {
        if ($this->max_usages === 0) {
            return false;
        }
        return $this->uses >= $this->max_usages;
    }

    /**
     * Invites used once.
     * @param Builder $query
     * @return Builder
     */
    public function scopeUsedOnce(Builder $query) : Builder
    {
        return $query->where('uses', '=', 1);
    }

    /**
     * Invites never used.
     * @param Builder $query
     * @return Builder
     */
    public function scopeNeverUsed(Builder $query) : Builder
    {
        return $query->where('uses', '=', 0);
    }

    /**
     * Most used invite code.
     * @param Builder $query
     * @return Builder
     */
    public function scopeMostUsed(Builder $query) : Builder
    {
        return $query->orderBy('uses','desc')->limit(1);
    }

    /**
     * Expired invites.
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query) : Builder
    {
        return $query->where('expires_at', '<', Carbon::now(config('app.timezone')));
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeSoldOut(Builder $query) : Builder
    {
        return $query->whereNotNull('max_usages')
            ->whereColumn('uses', '=', 'max_usages');
    }


}
