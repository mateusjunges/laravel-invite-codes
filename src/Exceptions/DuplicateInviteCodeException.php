<?php

namespace Junges\Watchdog\Exceptions;

class DuplicateInviteCodeException extends WatchdogException
{
    public static function forEmail(string $email = null)
    {
        throw new static("You can't create more than one invite for each email");
    }
}
