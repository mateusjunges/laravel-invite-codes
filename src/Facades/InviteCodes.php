<?php

namespace Junges\InviteCodes\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Junges\InviteCodes\Contracts\InviteCodesFactory;
use Junges\InviteCodes\Models\Invite;

/**
 * Class Factory.
 *
 * @method static $this withoutEvents() Will dispatch no events.
 * @method static $this redeem(string $code) Redeem an invite code.
 * @method static InviteCodesFactory create() Create an invite code.
 * @method static $this maxUsages(int $usages = null) Set the max allowed usages for invite codes.
 * @method static $this restrictUsageTo(string $email) Set the user who can use the invite code.
 * @method static $this expiresAt($date) Set the invite code expiration date.
 * @method static $this expiresIn(int $days) Set the invite code expiration date to $days from now.
 * @method static Invite save() Save the invite code.
 * @method static Collection make(int $quantity) Save $quantity invite codes.
 * @method static void macro($name, $macro)
 * @method static bool hasMacro($name)
 * @method static void quietly(callable $callback) Run the given callback without dispatching events
 * @method static void createInviteCodeUsing(?callable $callable)
 * @method static $this canBeUsedOnce()
 */
class InviteCodes extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return InviteCodesFactory::class;
    }
}
