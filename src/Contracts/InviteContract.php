<?php

namespace Junges\InviteCodes\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface InviteContract
{
    /** Check if an invite code can be used redeemed.*/
    public function canBeRedeemed(): bool;

    /** Check if an invite is to the user who has the specified email. */
    public function usageRestrictedToEmail(string $email): bool;

    /** Check if an invite is usable for only one person. */
    public function hasRestrictedUsage(): bool;

    /** Checks if the invite code is expired. */
    public function isExpired(): bool;

    /** Check if the invite code has been sold out. */
    public function isSoldOut(): bool;

    /** Invites used once. */
    public function scopeUsedOnce(Builder $query): Builder;

    /** Invites never used. */
    public function scopeNeverUsed(Builder $query): Builder;

    /** Most used invite code. */
    public function scopeMostUsed(Builder $query): Builder;

    /** Applies an Expired scope to the query. */
    public function scopeExpired(Builder $query): Builder;

    /** Applies a soldOut scope to the query. */
    public function scopeSoldOut(Builder $query): Builder;
}
