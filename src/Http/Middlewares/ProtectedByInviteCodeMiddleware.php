<?php

namespace Junges\InviteCodes\Http\Middlewares;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Junges\InviteCodes\Exceptions\InvalidInviteCodeException;
use Junges\InviteCodes\Exceptions\InviteWithRestrictedUsageException;
use Junges\InviteCodes\Exceptions\RouteProtectedByInviteCodeException;
use Junges\InviteCodes\Exceptions\UserLoggedOutException;
use Junges\InviteCodes\Facades\InviteCodes;
use Junges\InviteCodes\Http\Models\Invite;
use Symfony\Component\HttpFoundation\Response;

class ProtectedByInviteCodeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws RouteProtectedByInviteCodeException
     * @throws InvalidInviteCodeException
     * @throws UserLoggedOutException
     * @throws InviteWithRestrictedUsageException
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('invite_code')) {
            $invite_code = $request->input('invite_code');
            $invite_model = app(config('invite-codes.models.invite_model'));

            try {
                /** @var Invite $invite */
                $invite = $invite_model->where('code', $invite_code)->firstOrFail();
            } catch (ModelNotFoundException $exception) {
                throw new InvalidInviteCodeException('Your invite code is invalid', Response::HTTP_FORBIDDEN);
            }

            if ($invite->hasRestrictedUsage()) {
                if (! Auth::check()) {
                    throw new UserLoggedOutException('You must be logged in to use this invite code', Response::HTTP_FORBIDDEN);
                }
                if ($invite->usageRestrictedToEmail(Auth::user()->{config('invite-codes.user.email_column')})) {
                    InviteCodes::redeem($invite_code);

                    return $next($request);
                }

                throw new InviteWithRestrictedUsageException('This invite code is not for you.', Response::HTTP_FORBIDDEN);
            }
        } else {
            throw new RouteProtectedByInviteCodeException('This route is accessible only by using invite codes', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
