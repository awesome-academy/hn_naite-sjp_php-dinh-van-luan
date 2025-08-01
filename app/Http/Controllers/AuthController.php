<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use App\Helpers\ApiResponse;
use App\Enums\HttpStatusCode;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error(__('auth.login_failed'), [], HttpStatusCode::UNAUTHORIZED);
        }

        $user = Auth::user();

        $user->tokens()->where('name', 'access_token')->delete();

        $accessTokenExpiresAt  = Carbon::now()->addHours(1);
        $refreshTokenExpiresAt = Carbon::now()->addDays(15);

        $accessToken = $user->createToken('access_token', ['*']);
        $accessToken->accessToken->expires_at = $accessTokenExpiresAt;
        $accessToken->accessToken->save();

        $refreshToken = $user->createToken('refresh_token', ['refresh']);
        $refreshToken->accessToken->expires_at = $refreshTokenExpiresAt;
        $refreshToken->accessToken->save();

        return ApiResponse::success([
            'access_token'             => $accessToken->plainTextToken,
            'access_token_expires_at' => $accessTokenExpiresAt,
            'refresh_token'           => $refreshToken->plainTextToken,
            'refresh_token_expires_at' => $refreshTokenExpiresAt,
            'token_type'              => 'Bearer',
        ], __('auth.login_success'));
    }

    public function refreshToken(Request $request)
    {
        $currentRefreshToken = $request->bearerToken();
        $refreshToken = PersonalAccessToken::findToken($currentRefreshToken);

        if (
            !$refreshToken ||
            !$refreshToken->can('refresh') ||
            Carbon::parse($refreshToken->expires_at)->isPast()
        ) {
            return ApiResponse::error(__('auth.refresh_token_invalid'), [], HttpStatusCode::UNAUTHORIZED);
        }

        $user = $refreshToken->tokenable;
        $refreshToken->delete();

        $accessTokenExpiresAt  = Carbon::now()->addDays(1);
        $refreshTokenExpiresAt = Carbon::now()->addDays(7);

        $newAccessToken  = $user->createToken('access_token', ['*']);
        $newRefreshToken = $user->createToken('refresh_token', ['refresh']);

        $newAccessToken->accessToken->expires_at = $accessTokenExpiresAt;
        $newAccessToken->accessToken->save();

        $newRefreshToken->accessToken->expires_at = $refreshTokenExpiresAt;
        $newRefreshToken->accessToken->save();

        return ApiResponse::success([
            'access_token'             => $newAccessToken->plainTextToken,
            'access_token_expires_at' => $accessTokenExpiresAt,
            'refresh_token'           => $newRefreshToken->plainTextToken,
            'refresh_token_expires_at' => $refreshTokenExpiresAt,
            'token_type'              => 'Bearer',
        ], __('auth.refresh_token_success'));
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ApiResponse::success([], __('auth.logout_success'));
    }

    public function profile(Request $request)
    {
        return ApiResponse::success([
            'user' => $request->user(),
        ]);
    }
}
