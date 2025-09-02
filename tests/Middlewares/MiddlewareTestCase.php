<?php declare(strict_types=1);

namespace Junges\InviteCodes\Tests\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Junges\InviteCodes\Exceptions\InviteCodesException;
use Junges\InviteCodes\Http\Middlewares\ProtectedByInviteCodeMiddleware;
use Junges\InviteCodes\Tests\TestCase;

class MiddlewareTestCase extends TestCase
{
    protected $protected_by_invite_codes;

    protected function setUp(): void
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
        } catch (InviteCodesException $exception) {
            return $exception->getCode();
        }
    }
}
