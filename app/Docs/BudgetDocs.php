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
 *
 * @OA\Get(
 *     path="/api/budget/get",
 *     tags={"Budget"},
 *     summary="Lấy danh sách ngân sách theo người dùng",
 *     description="Truy xuất danh sách budget của người dùng hiện tại. Hỗ trợ lọc theo nhiều điều kiện khác nhau và phân trang. Cần token xác thực.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(name="wallet_use_scope", in="query", required=false, @OA\Schema(type="string", enum={"total", "wallet"}, example="wallet")),
 *     @OA\Parameter(name="wallet_id", in="query", required=false, @OA\Schema(type="integer", example=2)),
 *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer", example=3)),
 *     @OA\Parameter(name="is_recurring", in="query", required=false, @OA\Schema(type="boolean", example=true)),
 *     @OA\Parameter(name="recurring_type", in="query", required=false, @OA\Schema(type="string", enum={"weekly", "monthly", "quarterly"}, example="monthly")),
 *     @OA\Parameter(name="start_date", in="query", required=false, @OA\Schema(type="string", format="date", example="2025-01-01")),
 *     @OA\Parameter(name="end_date", in="query", required=false, @OA\Schema(type="string", format="date", example="2025-12-31")),
 *     @OA\Parameter(name="limit_amount_from", in="query", required=false, @OA\Schema(type="number", example=100)),
 *     @OA\Parameter(name="limit_amount_to", in="query", required=false, @OA\Schema(type="number", example=1000)),
 *     @OA\Parameter(name="spent_amount_from", in="query", required=false, @OA\Schema(type="number", example=50)),
 *     @OA\Parameter(name="spent_amount_to", in="query", required=false, @OA\Schema(type="number", example=900)),
 *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=10, minimum=1, maximum=100)),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Lấy danh sách ngân sách thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="OK"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="budgets", type="object",
 *                     @OA\Property(property="current_page", type="integer", example=1),
 *                     @OA\Property(property="data", type="array",
 *                         @OA\Items(type="object",
 *                             @OA\Property(property="id", type="integer", example=12),
 *                             @OA\Property(property="category_id", type="integer", example=3),
 *                             @OA\Property(property="wallet_use_scope", type="string", example="wallet"),
 *                             @OA\Property(property="wallet_id", type="integer", nullable=true, example=2),
 *                             @OA\Property(property="limit_amount", type="number", example=500),
 *                             @OA\Property(property="spent_amount", type="number", example=100),
 *                             @OA\Property(property="is_recurring", type="boolean", example=false),
 *                             @OA\Property(property="recurring_type", type="string", nullable=true, example="monthly"),
 *                             @OA\Property(property="start_date", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
 *                             @OA\Property(property="end_date", type="string", format="date-time", example="2025-12-31T23:59:59Z"),
 *                             @OA\Property(property="category", type="object",
 *                                 @OA\Property(property="id", type="integer", example=3),
 *                                 @OA\Property(property="name", type="string", example="Ăn uống"),
 *                                 @OA\Property(property="type", type="string", example="expense")
 *                             )
 *                         )
 *                     ),
 *                     @OA\Property(property="total", type="integer", example=1),
 *                     @OA\Property(property="per_page", type="integer", example=10),
 *                     @OA\Property(property="last_page", type="integer", example=1),
 *                     @OA\Property(property="from", type="integer", example=1),
 *                     @OA\Property(property="to", type="integer", example=1)
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Tham số truy vấn không hợp lệ",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Tham số truy vấn không hợp lệ"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="wallet_use_scope", type="array", @OA\Items(type="string", example="The selected wallet_use_scope is invalid.")),
 *                 @OA\Property(property="invalid_keys", type="array", @OA\Items(type="string", example="unknown_param"))
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi server khi truy vấn ngân sách",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Không thể lấy danh sách ngân sách"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     )
 * )
 */
class BudgetDocs
{
}
