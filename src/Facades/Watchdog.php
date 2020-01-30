<?php

namespace Junges\Watchdog\Facades;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Junges\Watchdog\Http\Models\Invite;

/**
 * Class Watchdog
 * @package Junges\Watchdog\Facades
 * @method static $this redeem(string $code)
 * @method static $this create()
 * @method static $this maxUsages(int $number = null)
 * @method static $this to(string $email)
 * @method static $this expiresAt($date)
 * @method static $this expiresIn(int $days)
 * @method static Invite save()
 * @method static Collection make(int $quantity)
 */
class Watchdog extends Facade
{
    public static function getFacadeAccessor() : string
    {
        return 'watchdog';
    }
}
