<?php

namespace Junges\Watchdog\Tests\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Junges\Watchdog\Exceptions\WatchdogException;
use Junges\Watchdog\Http\Middlewares\ProtectedByInviteCodeMiddleware;
use Junges\Watchdog\Tests\TestCase;

class MiddlewareTestCase extends TestCase
{
    protected $protected_by_invite_codes;

    public function setUp(): void
    {
        parent::setUp();

        $this->protected_by_invite_codes = new ProtectedByInviteCodeMiddleware();
    }

    public function execMiddleware($middleware, $params, $request = null)
    {
        $request ??= new Request();

        try {
            return $middleware->handle($request, function () {
                return (new Response())->setContent('<html></html>');
            }, $params)->status();
        } catch (WatchdogException $exception) {
            return $exception->getCode();
        }
    }
}
