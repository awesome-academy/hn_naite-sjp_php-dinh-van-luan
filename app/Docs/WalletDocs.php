<?php

namespace App\Docs;

/**
 * @OA\Post(
 *     path="/api/wallet",
 *     tags={"Wallet"},
 *     summary="Tạo ví mới",
 *     description="Tạo ví mới với loại: basic, saving, credit. Cần token xác thực.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     required={"name", "balance", "currency_id", "wallet_type"},
 *                     @OA\Property(property="name", type="string", example="My Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=1000),
 *                     @OA\Property(property="currency_id", type="integer", example=1),
 *                     @OA\Property(property="wallet_type", type="string", enum={"basic"}, example="basic")
 *                 ),
 *                 @OA\Schema(
 *                     required={"name", "balance", "currency_id", "wallet_type", "target_amount", "end_date"},
 *                     @OA\Property(property="name", type="string", example="Saving Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=500),
 *                     @OA\Property(property="currency_id", type="integer", example=1),
 *                     @OA\Property(property="wallet_type", type="string", enum={"saving"}, example="saving"),
 *                     @OA\Property(property="target_amount", type="number", format="float", example=10000),
 *                     @OA\Property(property="end_date", type="string", format="date", example="2025-12-31")
 *                 ),
 *                 @OA\Schema(
 *                     required={"name", "balance", "currency_id", "wallet_type", "credit_limit", "statement_date", "payment_due_date"},
 *                     @OA\Property(property="name", type="string", example="Credit Card Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=200),
 *                     @OA\Property(property="currency_id", type="integer", example=1),
 *                     @OA\Property(property="wallet_type", type="string", enum={"credit"}, example="credit"),
 *                     @OA\Property(property="credit_limit", type="number", format="float", example=5000),
 *                     @OA\Property(property="statement_date", type="string", format="date", example="2025-07-01"),
 *                     @OA\Property(property="payment_due_date", type="string", format="date", example="2025-07-15")
 *                 )
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Tạo ví thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="wallet", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="My Wallet"),
 *                 @OA\Property(property="balance", type="number", format="float", example=1000),
 *                 @OA\Property(property="wallet_type", type="string", example="basic"),
 *                 @OA\Property(property="currency_id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dữ liệu không hợp lệ"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Tạo ví thất bại"
 *     )
 * )
 */
class WalletDocs
{
}
