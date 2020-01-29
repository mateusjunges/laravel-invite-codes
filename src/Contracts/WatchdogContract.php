<?php

namespace Junges\Watchdog\Contracts;

use Junges\Watchdog\Http\Models\Invite;

interface WatchdogContract
{
    function redeem($code);

    function verify();

    static function create();

    /**
     * @param Invite $invite
     * @param string|null $email
     * @return mixed
     */
    function inviteCanBeRedeemed(Invite $invite, string $email = null);
}
