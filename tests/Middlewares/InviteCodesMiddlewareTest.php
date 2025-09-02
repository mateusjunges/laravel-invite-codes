<?php declare(strict_types=1);

namespace Junges\InviteCodes\Tests\Middlewares;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Tests\TestUser;

class InviteCodesMiddlewareTest extends MiddlewareTestCase
{
    public function test_if_a_user_without_invite_codes_can_access_protected_routes()
    {
        $this->assertEquals(
            $this->execMiddleware($this->protected_by_invite_codes, null),
            Response::HTTP_FORBIDDEN
        );
    }

    public function test_if_user_with_invalid_invite_code_can_access_a_protected_route()
    {
        $this->assertEquals(
            $this->execMiddleware($this->protected_by_invite_codes, [
                'invite_code' => 'INVALID-INVITE-CODE',
            ]),
            Response::HTTP_FORBIDDEN
        );
    }

    public function test_if_valid_invite_code_grants_access_to_protected_routes()
    {
        $invite = InviteCodes::create()
            ->expiresAt(Carbon::now()->addDay(10))
            ->save();

        $request = new Request();

        $request->merge([
            'invite_code' => $invite->code,
        ]);

        $this->assertEquals(
            $this->execMiddleware($this->protected_by_invite_codes, null, $request),
            Response::HTTP_OK
        );
    }

    public function test_if_not_logged_in_users_can_use_restricted_invite_codes()
    {
        $invite = InviteCodes::create()
            ->expiresAt(Carbon::now()->addDay(10))
            ->restrictUsageTo('contato@mateusjunges.com')
            ->save();

        $request = new Request();

        $request->merge([
            'invite_code' => $invite->code,
        ]);

        $this->assertEquals(
            $this->execMiddleware($this->protected_by_invite_codes, null, $request),
            Response::HTTP_FORBIDDEN
        );
    }

    public function test_if_logged_in_users_can_use_restricted_invite_codes()
    {
        $invite = InviteCodes::create()
            ->expiresAt(Carbon::now()->addDay(10))
            ->restrictUsageTo('contato@mateusjunges.com')
            ->save();

        $request = new Request();

        $request->merge([
            'invite_code' => $invite->code,
        ]);

        $user = TestUser::create([
            'email' => 'contato@mateusjunges.com',
            'name' => 'Mateus Junges',
        ]);

        Auth::login($user);

        $this->assertEquals(
            Response::HTTP_OK,
            $this->execMiddleware($this->protected_by_invite_codes, null, $request),
        );
    }

    public function test_if_a_logged_in_user_with_invalid_invite_code_can_not_access_protected_routes()
    {
        $request = new Request();

        $request->merge([
            'invite_code' => 'INVALID-INVITE-CODE',
        ]);

        $user = TestUser::create([
            'email' => 'contato@mateusjunges.com',
            'name' => 'Mateus Junges',
        ]);

        Auth::login($user);

        $this->assertEquals(
            $this->execMiddleware($this->protected_by_invite_codes, null, $request),
            Response::HTTP_FORBIDDEN
        );
    }
}
