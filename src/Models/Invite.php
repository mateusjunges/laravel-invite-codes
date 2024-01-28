<?php

namespace Junges\InviteCodes\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Junges\InviteCodes\Contracts\InviteContract;
use Junges\InviteCodes\Events\InviteCreatedEvent;

/**
 * Class Invite.
 *
 * @property int $id The model primary key
 * @property string $code The unique code for this invite
 * @property int|null $max_usages The maximum number of times this invite code can be used
 * @property string|null $to The email of the person who can use this invite code
 * @property int $uses The number of times this invite code have been used
 * @property \Illuminate\Support\Carbon $expires_at The date this invite code expires
 * @property \Illuminate\Support\Carbon $updated_at The date this invite code was last updated
 * @property \Illuminate\Support\Carbon $created_at The date this invite code was created
 * @property \Illuminate\Support\Carbon $deleted_at The date this invite code has been deleted
 * @method static Builder usedOnce() All invites used once.
 * @method static Builder neverUsed() All never used invites.
 * @method static Builder mostUsed() The most used invite.
 * @method static Builder expired() All expired invites.
 * @method static Builder soldOut() All sold out invites.
 */
class Invite extends Model implements InviteContract
{
    protected $table;

    protected $casts = [
        'deleted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

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

    /** Sets 'code' as primary key for Route Model Bindings. */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** Check if an invite code can be used redeemed. */
    public function canBeRedeemed(): bool
    {
        return ! $this->isExpired() && ! $this->isSoldOut() && ! $this->hasRestrictedUsage();
    }

    /** Check if an invite is restricted to the user who has the specified email. */
    public function usageRestrictedToEmail(string $email): bool
    {
        return $this->to === $email;
    }

    /** Check if an invite is usable for only one person. */
    public function hasRestrictedUsage(): bool
    {
        return $this->to !== null;
    }

    /** Checks if the invite code is expired. */
    public function isExpired(): bool
    {
        if (empty($this->expires_at)) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /** Check if the invite code has been sold out. */
    public function isSoldOut(): bool
    {
        if ($this->max_usages === null) {
            return false;
        }

        return $this->uses >= $this->max_usages;
    }

    public function scopeUsedOnce(Builder $query): Builder
    {
        return $query->where('uses', '=', 1);
    }

    public function scopeNeverUsed(Builder $query): Builder
    {
        return $query->where('uses', '=', 0);
    }

    public function scopeMostUsed(Builder $query): Builder
    {
        return $query->orderBy('uses', 'desc')->limit(1);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', Carbon::now(config('app.timezone')));
    }

    public function scopeSoldOut(Builder $query): Builder
    {
        return $query->whereNotNull('max_usages')
            ->whereColumn('uses', '=', 'max_usages');
    }
}
