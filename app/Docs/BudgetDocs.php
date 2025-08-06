<?php

namespace App\Docs;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/budget/create",
 *     summary="Tạo ngân sách mới hoặc cập nhật nếu đã tồn tại",
 *     description="Tạo ngân sách theo category, loại ví, khoảng thời gian, có thể theo chu kỳ (recurring).",
 *     tags={"Budget"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"category_id", "limit_amount", "spent_amount", "wallet_use_scope", "is_recurring", "recurring_type", "start_date", "end_date"},
 *             @OA\Property(property="category_id", type="integer", example=3),
 *             @OA\Property(property="limit_amount", type="number", example=5000),
 *             @OA\Property(property="spent_amount", type="number", example=1000),
 *             @OA\Property(property="wallet_use_scope", type="string", enum={"wallet", "total"}, example="wallet"),
 *             @OA\Property(property="wallet_id", type="integer", nullable=true, example=1),
 *             @OA\Property(property="is_recurring", type="boolean", example=true),
 *             @OA\Property(property="recurring_type", type="string", enum={"weekly", "monthly", "quarterly", "yearly", "custom"}, example="monthly"),
 *             @OA\Property(property="start_date", type="string", format="date", example="2025-08-01"),
 *             @OA\Property(property="end_date", type="string", format="date", example="2025-08-31")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Ngân sách đã được tạo hoặc cập nhật",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Ngân sách đã được tạo thành công"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="budget", type="object",
 *                     @OA\Property(property="id", type="integer", example=12),
 *                     @OA\Property(property="user_id", type="integer", example=5),
 *                     @OA\Property(property="category_id", type="integer", example=3),
 *                     @OA\Property(property="limit_amount", type="number", example=5000),
 *                     @OA\Property(property="spent_amount", type="number", example=1000),
 *                     @OA\Property(property="wallet_use_scope", type="string", example="wallet"),
 *                     @OA\Property(property="wallet_id", type="integer", example=1),
 *                     @OA\Property(property="is_recurring", type="boolean", example=true),
 *                     @OA\Property(property="recurring_type", type="string", example="monthly"),
 *                     @OA\Property(property="start_date", type="string", format="date", example="2025-08-01"),
 *                     @OA\Property(property="end_date", type="string", format="date", example="2025-08-31"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-05T10:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-05T10:00:00Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Dữ liệu không hợp lệ hoặc thiếu",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="category_id", type="array", @OA\Items(type="string", example="Trường category_id là bắt buộc.")),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi hệ thống khi tạo ngân sách",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Tạo ngân sách thất bại"),
 *         )
 *     )
 * )
 */
class BudgetDocs
{
}
