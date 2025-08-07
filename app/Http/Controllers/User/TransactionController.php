<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Enums\HttpStatusCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Budget\BudgetService;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\Transaction\TransactionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionController extends Controller
{
    protected int $perPage;

    public function __construct()
    {
        $this->perPage = config("paginate")["per_page"] ?? 10;
    }

    public function create(Request $request, BudgetService $budgetService, TransactionService $transactionService)
    {
        try {
            $validated = $request->validate([
                'wallet_id'                => 'required|exists:wallets,id',
                'category_id'              => 'required|exists:categories,id',
                'amount'                   => 'required|numeric|min:0.01',
                'note'                     => 'nullable|string|max:255',
                'date'                     => 'required|date',
            ]);

            $user = auth()->user();

            DB::beginTransaction();

            $transaction = $transactionService->createTransaction($validated, $user);

            $budgetService->updateBudgetsAfterTransaction($transaction, $user);

            DB::commit();

            return ApiResponse::success([
                'transaction' => $transaction
            ], __('transaction.created_successfully'), HttpStatusCode::CREATED);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiResponse::error(__('transaction.invalid_data'), $e->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\RuntimeException $e) {
            DB::rollBack();
            Log::error('Transaction creation failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('transaction.created_failed'), [], HttpStatusCode::BAD_REQUEST);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction creation failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('transaction.created_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function getTransactionsByUser(Request $request)
    {
        try {
            $user = $request->user();

            $validQueryKeys = ['wallet_id', 'category_id', 'date', 'per_page', 'page'];
            $queryParams = $request->only($validQueryKeys);
            $diff = array_diff(array_keys($request->all()), array_keys($queryParams));

            if (!empty($diff)) {
                return ApiResponse::error(__('transaction.invalid_query'), [
                    'invalid_keys' => $diff
                ], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $validator = Validator::make($queryParams, [
                'wallet_id'    => 'nullable|integer|exists:wallets,id',
                'category_id'  => 'nullable|integer|exists:categories,id',
                'date'         => 'nullable|date',
                'per_page'     => 'nullable|integer|min:1|max:100',
                'page'         => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(__('transaction.invalid_query'), $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $perPage = $request->query('per_page', $this->perPage);

            $cacheKey = 'transactions:user:' . $user->id . ':filters:' . md5(json_encode($queryParams) . $perPage);

            $transactions = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($request, $user, $perPage) {
                return Transaction::query()
                    ->whereHas('wallet', fn ($q) => $q->where('user_id', $user->id))
                    ->when($request->wallet_id, fn ($q) => $q->where('wallet_id', $request->wallet_id))
                    ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
                    ->when($request->date, fn ($q) => $q->whereDate('date', $request->date))
                    ->with(['wallet', 'category'])
                    ->orderByDesc('date')
                    ->paginate($perPage);
            });

            return ApiResponse::success([
                'transactions' => $transactions
            ], HttpStatusCode::OK);
        } catch (\Throwable $e) {
            Log::error('Fetch transactions failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('transaction.fetch_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function getTransactionById(int $id)
    {
        try {
            $user = auth()->user();

            $transaction = Transaction::with(['wallet', 'category'])->findOrFail($id);

            if ($transaction->wallet->user_id !== $user->id) {
                return ApiResponse::error(__('transaction.unauthorized_access'), [], HttpStatusCode::FORBIDDEN);
            }

            return ApiResponse::success([
                'transaction' => $transaction
            ], __('transaction.fetched_successfully'), HttpStatusCode::OK);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(__('transaction.not_found'), [], HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Fetch transaction detail failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('transaction.fetch_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
