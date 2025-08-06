<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/transaction/create",
 *     tags={"Transaction"},
 *     summary="Tạo mới một giao dịch chi tiêu",
 *     operationId="createTransaction",
 *     security={{"sanctum":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"wallet_id", "category_id", "amount", "date"},
 *             @OA\Property(property="wallet_id", type="integer", example=3, description="ID của ví cần dùng"),
 *             @OA\Property(property="category_id", type="integer", example=5, description="ID danh mục chi tiêu"),
 *             @OA\Property(property="amount", type="number", format="float", example=100000, description="Số tiền chi"),
 *             @OA\Property(property="note", type="string", example="Đi ăn trưa với bạn", description="Ghi chú (tùy chọn)"),
 *             @OA\Property(property="date", type="string", format="date", example="2025-08-05", description="Ngày giao dịch"),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Giao dịch tạo thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Giao dịch tạo thành công"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="transaction", type="object",
 *                     @OA\Property(property="id", type="integer", example=12),
 *                     @OA\Property(property="user_id", type="integer", example=1),
 *                     @OA\Property(property="wallet_id", type="integer", example=3),
 *                     @OA\Property(property="category_id", type="integer", example=5),
 *                     @OA\Property(property="amount", type="number", format="float", example=100000),
 *                     @OA\Property(property="note", type="string", example="Đi ăn trưa với bạn"),
 *                     @OA\Property(property="date", type="string", format="date", example="2025-08-05"),
 *                     @OA\Property(property="created_at", type="string", example="2025-08-05T08:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", example="2025-08-05T08:00:00Z"),
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Số dư không đủ",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Số dư ví không đủ")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Không có quyền truy cập ví",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Không được phép thao tác với ví này")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Dữ liệu không hợp lệ",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="amount", type="array", @OA\Items(type="string", example="Số tiền phải lớn hơn 0"))
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi hệ thống khi tạo giao dịch",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Tạo giao dịch thất bại")
 *         )
 *     )
 * )
 */
class TransactionDocs
{
}
