<?php

namespace Junges\InviteCodes\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Junges\InviteCodes\Contracts\InviteCodesFactory;
use Junges\InviteCodes\Contracts\InviteContract;
use Junges\InviteCodes\Models\Invite;

/**
 * Class Factory.
 *
 * @method static InviteCodesFactory withoutEvents() Will dispatch no events.
 * @method static InviteContract redeem(string $code) Redeem an invite code.
 * @method static InviteCodesFactory create() Create an invite code.
 * @method static InviteCodesFactory maxUsages(int $usages = null) Set the max allowed usages for invite codes.
 * @method static InviteCodesFactory restrictUsageTo(string $email) Set the user who can use the invite code.
 * @method static InviteCodesFactory expiresAt($date) Set the invite code expiration date.
 * @method static InviteCodesFactory expiresIn(int $days) Set the invite code expiration date to $days from now.
 * @method static Invite save() Save the invite code.
 * @method static Collection<int, InviteContract> make(int $quantity) Save $quantity invite codes.
 * @method static void macro($name, $macro)
 * @method static bool hasMacro($name)
 * @method static void createInviteCodeUsing(?callable $callable = null)
 * @method static InviteCodesFactory canBeUsedOnce()
 */
class InviteCodes extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return InviteCodesFactory::class;
    }
}
