<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;

class CheckAccessTokenExpiry
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($token);

        if (
            !$accessToken ||
            ($accessToken->expires_at && Carbon::parse($accessToken->expires_at)->isPast())
        ) {

            return ApiResponse::error(__('auth.token_expired'), [], HttpStatusCode::UNAUTHORIZED);
        }

        return $next($request);
    }
}
