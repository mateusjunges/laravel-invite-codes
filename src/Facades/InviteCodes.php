<?php

namespace Junges\InviteCodes\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Junges\InviteCodes\Http\Models\Invite;

/**
 * Class InviteCodes.
 *
 * @method static $this withoutEvents() Will dispatch no events.
 * @method static $this redeem(string $code) Redeem an invite code.
 * @method static $this create() Create a invite code.
 * @method static $this maxUsages(int $usages = null) Set the max allowed usages for invite codes.
 * @method static $this restrictUsageTo(string $email) Set the user who can use the invite code.
 * @method static $this expiresAt($date) Set the invite code expiration date.
 * @method static $this expiresIn(int $days) Set the invite code expiration date to $days from now.
 * @method static Invite save() Save the invite code.
 * @method static Collection make(int $quantity) Save $quantity invite codes.
 * @method static $this canBeUsedOnce()
 */
class InviteCodes extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'invite_codes';
    }
}
