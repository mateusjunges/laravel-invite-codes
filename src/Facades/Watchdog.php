<?php

namespace Junges\Watchdog\Facades;

use Illuminate\Support\Facades\Facade;

class Watchdog extends Facade
{
    public static function getFacadeAccessor() : string
    {
        return 'watchdog';
    }
}
