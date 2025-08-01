<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Localhost server"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Bearer Token dùng cho xác thực",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 */
class AuthDocs
{
}

/**
 * Login endpoint
 *
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="Đăng nhập tài khoản",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="12345678")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Đăng nhập thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="refresh_token", type="string"),
 *             @OA\Property(property="token_type", type="string"),
 *             @OA\Property(property="access_token_expires_at", type="string", format="date-time"),
 *             @OA\Property(property="refresh_token_expires_at", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Đăng nhập thất bại"
 *     )
 * )
 */
class AuthLoginDocs
{
}

/**
 * Refresh token
 *
 * @OA\Post(
 *     path="/api/refresh",
 *     tags={"Auth"},
 *     summary="Làm mới access token",
 *     description="Dùng refresh_token hợp lệ để lấy access_token mới và refresh_token mới.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="access_token", type="string"),
 *             @OA\Property(property="refresh_token", type="string"),
 *             @OA\Property(property="access_token_expires_at", type="string", format="date-time"),
 *             @OA\Property(property="refresh_token_expires_at", type="string", format="date-time"),
 *             @OA\Property(property="token_type", type="string", example="Bearer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Refresh token không hợp lệ hoặc hết hạn"
 *     )
 * )
 */
class AuthRefreshDocs
{
}

/**
 * Get profile
 *
 * @OA\Get(
 *     path="/api/profile",
 *     tags={"Auth"},
 *     summary="Lấy thông tin người dùng",
 *     description="Trả về thông tin của người dùng đang đăng nhập.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Thông tin người dùng",
 *         @OA\JsonContent(
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Luan"),
 *                 @OA\Property(property="email", type="string", example="luan@example.com")
 *             )
 *         )
 *     )
 * )
 */
class AuthProfileDocs
{
}
