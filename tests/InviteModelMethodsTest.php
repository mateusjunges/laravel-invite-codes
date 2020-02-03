<?php

namespace Junges\Watchdog\Tests;

use Junges\Watchdog\Facades\Watchdog;

class InviteModelMethodsTest extends TestCase
{
    public function test_is_sold_out_method()
    {
        $invite = Watchdog::create()
            ->canBeUsedOnce()
            ->save();

        Watchdog::redeem($invite->code);
        
        $this->assertTrue($invite->isSoldOut());
    }
}
