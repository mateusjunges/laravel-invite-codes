<?php

namespace Junges\InviteCodes\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface InviteContract
{
    /**
     * Check if an invite code can be used redeemed.
     *
     * @return bool
     */
    public function canBeRedeemed(): bool;

    /**
     * Check if an invite is to the user who has the specified email.
     *
     * @param $email
     * @return bool
     */
    public function usageRestrictedToEmail(string $email): bool;

    /**
     * Check if an invite is usable for only one person.
     *
     * @return bool
     */
    public function hasRestrictedUsage(): bool;

    /**
     * Checks if the invite code is expired.
     *
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * Check if the invite code has been sold out.
     *
     * @return bool
     */
    public function isSoldOut(): bool;

    /**
     * Invites used once.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeUsedOnce(Builder $query): Builder;

    /**
     * Invites never used.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeNeverUsed(Builder $query): Builder;

    /**
     * Most used invite code.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeMostUsed(Builder $query): Builder;

    /**
     * Expired invites.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder;

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSoldOut(Builder $query): Builder;
}
