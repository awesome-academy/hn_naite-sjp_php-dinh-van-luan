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
 *
 * @OA\Get(
 *     path="/api/wallet/by-user",
 *     tags={"Wallet"},
 *     summary="Lấy danh sách ví theo người dùng",
 *     description="Lấy danh sách ví của user hiện tại hoặc theo user_id truyền vào (nếu có). Bao gồm cả ví saving và credit.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="query",
 *         required=false,
 *         description="ID người dùng (nếu không có sẽ lấy từ token)",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Danh sách ví thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="wallets", type="array",
 *                 @OA\Items(type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="My Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=1000),
 *                     @OA\Property(property="wallet_type", type="string", example="saving"),
 *                     @OA\Property(property="currency_id", type="integer", example=1),
 *                     @OA\Property(property="saving_wallet", type="object", nullable=true),
 *                     @OA\Property(property="credit_wallet", type="object", nullable=true)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=500, description="Lỗi server")
 * )
 *
 * @OA\Get(
 *     path="/api/wallet/get/{id}",
 *     tags={"Wallet"},
 *     summary="Lấy chi tiết ví",
 *     description="Trả về chi tiết ví theo ID. Bao gồm các thuộc tính mở rộng nếu là ví saving hoặc credit.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID của ví",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Chi tiết ví thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="wallet", type="object",
 *                 @OA\Property(property="id", type="integer", example=2),
 *                 @OA\Property(property="name", type="string", example="My Wallet"),
 *                 @OA\Property(property="balance", type="number", format="float", example=2000),
 *                 @OA\Property(property="wallet_type", type="string", example="credit"),
 *                 @OA\Property(property="currency_id", type="integer", example=1),
 *                 @OA\Property(property="saving_wallet", type="object", nullable=true),
 *                 @OA\Property(property="credit_wallet", type="object", nullable=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Không tìm thấy ví"),
 *     @OA\Response(response=500, description="Lỗi server")
 * )
 *
 * @OA\Put(
 *     path="/api/wallet/update/{id}",
 *     tags={"Wallet"},
 *     summary="Cập nhật ví theo loại",
 *     description="Cập nhật ví cơ bản, tiết kiệm hoặc tín dụng dựa trên loại ví hiện tại. Cần xác thực bearer token.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID của ví cần cập nhật",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Dữ liệu cập nhật tuỳ theo loại ví",
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(
 *                     description="Cập nhật ví cơ bản",
 *                     required={"name", "balance", "currency_id"},
 *                     @OA\Property(property="name", type="string", example="Updated Basic Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=1200),
 *                     @OA\Property(property="currency_id", type="integer", example=1)
 *                 ),
 *                 @OA\Schema(
 *                     description="Cập nhật ví tiết kiệm",
 *                     required={"name", "initial_amount", "currency_id", "target_amount", "end_date"},
 *                     @OA\Property(property="name", type="string", example="Updated Saving Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=2000),
 *                     @OA\Property(property="initial_amount", type="number", format="float", example=2000),
 *                     @OA\Property(property="currency_id", type="integer", example=1),
 *                     @OA\Property(property="target_amount", type="number", format="float", example=5000),
 *                     @OA\Property(property="end_date", type="string", format="date", example="2025-12-31")
 *                 ),
 *                 @OA\Schema(
 *                     description="Cập nhật ví tín dụng",
 *                     required={"name", "balance", "currency_id", "statement_date", "payment_due_date"},
 *                     @OA\Property(property="name", type="string", example="Updated Credit Wallet"),
 *                     @OA\Property(property="balance", type="number", format="float", example=300),
 *                     @OA\Property(property="currency_id", type="integer", example=1),
 *                     @OA\Property(property="credit_limit", type="number", format="float", example=2000),
 *                     @OA\Property(property="statement_date", type="string", format="date", example="2025-08-01"),
 *                     @OA\Property(property="payment_due_date", type="string", format="date", example="2025-08-15")
 *                 )
 *             }
 *         )
 *     ),
 * @OA\Response(
 *         response=200,
 *         description="Cập nhật thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Cập nhật ví thành công"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="wallet", oneOf={
 *                     @OA\Schema(
 *                         description="Ví cơ bản",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="user_id", type="integer", example=5),
 *                         @OA\Property(property="name", type="string", example="Basic Wallet"),
 *                         @OA\Property(property="balance", type="number", example=1000),
 *                         @OA\Property(property="currency_id", type="integer", example=1),
 *                         @OA\Property(property="wallet_type", type="string", example="basic"),
 *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-31T10:00:00Z"),
 *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-31T10:05:00Z")
 *                     ),
 *                     @OA\Schema(
 *                         description="Ví tiết kiệm",
 *                         @OA\Property(property="id", type="integer", example=2),
 *                         @OA\Property(property="user_id", type="integer", example=5),
 *                         @OA\Property(property="name", type="string", example="Saving Wallet"),
 *                         @OA\Property(property="balance", type="number", example=2000),
 *                         @OA\Property(property="currency_id", type="integer", example=1),
 *                         @OA\Property(property="wallet_type", type="string", example="saving"),
 *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-31T10:00:00Z"),
 *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-31T10:05:00Z"),
 *                         @OA\Property(property="saving_wallet", type="object",
 *                             @OA\Property(property="target_amount", type="number", example=10000),
 *                             @OA\Property(property="end_date", type="string", format="date", example="2025-12-31"),
 *                             @OA\Property(property="initial_amount", type="number", example=2000)
 *                         )
 *                     ),
 *                     @OA\Schema(
 *                         description="Ví tín dụng",
 *                         @OA\Property(property="id", type="integer", example=3),
 *                         @OA\Property(property="user_id", type="integer", example=5),
 *                         @OA\Property(property="name", type="string", example="Credit Wallet"),
 *                         @OA\Property(property="balance", type="number", example=500),
 *                         @OA\Property(property="currency_id", type="integer", example=1),
 *                         @OA\Property(property="wallet_type", type="string", example="credit"),
 *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-31T10:00:00Z"),
 *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-31T10:05:00Z"),
 *                         @OA\Property(property="credit_wallet", type="object",
 *                             @OA\Property(property="credit_limit", type="number", example=5000),
 *                             @OA\Property(property="statement_date", type="string", format="date", example="2025-08-01"),
 *                             @OA\Property(property="payment_due_date", type="string", format="date", example="2025-08-15")
 *                         )
 *                     )
 *                 })
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi hệ thống hoặc dữ liệu không hợp lệ",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Cập nhật ví thất bại"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     )
 * )
 */
class WalletDocs
{
}
