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
 *
 *  * @OA\Get(
 *     path="/api/transaction/get",
 *     tags={"Transaction"},
 *     summary="Lấy danh sách giao dịch theo người dùng",
 *     description="Lấy các giao dịch có thể lọc theo ví, danh mục, ngày. Cần token xác thực.",
 *     operationId="getTransactionsByUser",
 *     security={{"sanctum":{}}},
 *
 *     @OA\Parameter(
 *         name="wallet_id",
 *         in="query",
 *         required=false,
 *         description="ID của ví cần lọc",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Parameter(
 *         name="category_id",
 *         in="query",
 *         required=false,
 *         description="ID danh mục giao dịch",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=false,
 *         description="Ngày giao dịch (format YYYY-MM-DD)",
 *         @OA\Schema(type="string", format="date", example="2025-08-07")
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Số lượng bản ghi trên mỗi trang",
 *         @OA\Schema(type="integer", example=10)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Số trang cần lấy",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Lấy danh sách giao dịch thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Lấy danh sách giao dịch thành công"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="transactions", type="object",
 *                     @OA\Property(property="current_page", type="integer", example=1),
 *                     @OA\Property(property="total", type="integer", example=50),
 *                     @OA\Property(property="per_page", type="integer", example=10),
 *                     @OA\Property(property="data", type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="id", type="integer", example=1),
 *                             @OA\Property(property="wallet_id", type="integer", example=3),
 *                             @OA\Property(property="category_id", type="integer", example=5),
 *                             @OA\Property(property="amount", type="number", example=100000),
 *                             @OA\Property(property="note", type="string", example="Mua sách"),
 *                             @OA\Property(property="date", type="string", format="date", example="2025-08-01"),
 *                             @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-01T09:00:00Z"),
 *                             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-01T09:00:00Z"),
 *                             @OA\Property(property="wallet", type="object",
 *                                 @OA\Property(property="name", type="string", example="Ví chính")
 *                             ),
 *                             @OA\Property(property="category", type="object",
 *                                 @OA\Property(property="name", type="string", example="Ăn uống")
 *                             )
 *                         )
 *                     )
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
 *             @OA\Property(property="message", type="string", example="Tham số không hợp lệ"),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="wallet_id", type="array", @OA\Items(type="string", example="wallet_id không tồn tại"))
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi hệ thống khi truy vấn giao dịch",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Lỗi khi lấy danh sách giao dịch")
 *         )
 *     )
 * )
 *
 *  @OA\Get(
 *     path="/api/transaction/get/{id}",
 *     tags={"Transaction"},
 *     summary="Lấy chi tiết giao dịch theo ID",
 *     description="Lấy thông tin chi tiết của giao dịch. Yêu cầu xác thực token và chỉ xem được giao dịch thuộc ví của chính user.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID của giao dịch cần lấy",
 *         @OA\Schema(
 *             type="integer",
 *             example=12
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Lấy giao dịch thành công"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="transaction", type="object",
 *                     @OA\Property(property="id", type="integer", example=12),
 *                     @OA\Property(property="wallet_id", type="integer", example=3),
 *                     @OA\Property(property="category_id", type="integer", example=2),
 *                     @OA\Property(property="amount", type="number", format="float", example=50000),
 *                     @OA\Property(property="note", type="string", example="Thanh toán điện nước"),
 *                     @OA\Property(property="date", type="string", format="date", example="2025-08-01"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-01T10:00:00Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-01T10:00:00Z"),
 *                     @OA\Property(property="wallet", type="object",
 *                         @OA\Property(property="id", type="integer", example=3),
 *                         @OA\Property(property="name", type="string", example="My Wallet")
 *                     ),
 *                     @OA\Property(property="category", type="object",
 *                         @OA\Property(property="id", type="integer", example=2),
 *                         @OA\Property(property="name", type="string", example="Tiền nhà")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Không có quyền truy cập",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Bạn không có quyền truy cập giao dịch này"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Không tìm thấy giao dịch",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Không tìm thấy giao dịch"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi hệ thống",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Lỗi lấy dữ liệu giao dịch"),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     )
 * )
 */
class TransactionDocs
{
}
