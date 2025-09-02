<?php declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Tests\TestUser;

it('denies access to users without invite codes', function () {
    $result = execMiddleware(getMiddleware(), null);

    expect($result)->toBe(Response::HTTP_FORBIDDEN);
});

it('denies access to users with invalid invite codes', function () {
    $result = execMiddleware(getMiddleware(), [
        'invite_code' => 'INVALID-INVITE-CODE',
    ]);

    expect($result)->toBe(Response::HTTP_FORBIDDEN);
});

it('grants access with valid invite code', function () {
    $invite = InviteCodes::create()
        ->expiresAt(Carbon::now()->addDay(10))
        ->save();

    $request = new Request();
    $request->merge(['invite_code' => $invite->code]);

    $result = execMiddleware(getMiddleware(), null, $request);

    expect($result)->toBe(Response::HTTP_OK);
});

it('denies access to non-logged users with restricted invite codes', function () {
    $invite = InviteCodes::create()
        ->expiresAt(Carbon::now()->addDay(10))
        ->restrictUsageTo('contato@mateusjunges.com')
        ->save();

    $request = new Request();
    $request->merge(['invite_code' => $invite->code]);

    $result = execMiddleware(getMiddleware(), null, $request);

    expect($result)->toBe(Response::HTTP_FORBIDDEN);
});

it('grants access to logged in users with restricted invite codes', function () {
    $invite = InviteCodes::create()
        ->expiresAt(Carbon::now()->addDay(10))
        ->restrictUsageTo('contato@mateusjunges.com')
        ->save();

    $request = new Request();
    $request->merge(['invite_code' => $invite->code]);

    $user = TestUser::create([
        'email' => 'contato@mateusjunges.com',
        'name' => 'Mateus Junges',
    ]);

    Auth::login($user);

    $result = execMiddleware(getMiddleware(), null, $request);

    expect($result)->toBe(Response::HTTP_OK);
});

it('denies access to logged in users with invalid invite codes', function () {
    $request = new Request();
    $request->merge(['invite_code' => 'INVALID-INVITE-CODE']);

    $user = TestUser::create([
        'email' => 'contato@mateusjunges.com',
        'name' => 'Mateus Junges',
    ]);

    Auth::login($user);

    $result = execMiddleware(getMiddleware(), null, $request);

    expect($result)->toBe(Response::HTTP_FORBIDDEN);
});
