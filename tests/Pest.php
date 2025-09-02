<?php declare(strict_types=1);

use Junges\InviteCodes\Http\Middlewares\ProtectedByInviteCodeMiddleware;

pest()->uses(Junges\InviteCodes\Tests\TestCase::class);

pest()->beforeEach(function () {
    $this->setUpDatabase($this->app);
    new Junges\InviteCodes\InviteCodesServiceProvider($this->app)->boot();
});

function execMiddleware($middleware, $params, $request = null)
{
    $request ??= new Illuminate\Http\Request();

    try {
        return $middleware->handle($request, function () {
            return new Illuminate\Http\Response()->setContent('<html></html>');
        }, $params)->status();
    } catch (Junges\InviteCodes\Exceptions\InviteCodesException $exception) {
        return $exception->getCode();
    }
}

function getMiddleware(): ProtectedByInviteCodeMiddleware
{
    return new ProtectedByInviteCodeMiddleware;
}
