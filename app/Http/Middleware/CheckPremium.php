<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Constants\UserRoles;
use App\Helpers\ApiResponse;
use App\Enums\HttpStatusCode;

class CheckPremium
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole(UserRoles::PREMIUM_USER)) {
            return ApiResponse::error(__('auth.access_denied'), [], HttpStatusCode::FORBIDDEN);
        }

        return $next($request);
    }
}
