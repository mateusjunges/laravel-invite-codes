<?php

namespace Junges\InviteCodes\Exceptions;

class DuplicateInviteCodeException extends InviteCodesException
{
    public static function forEmail(): self
    {
        return new static("You can't create more than one invite for each email");
    }
}
