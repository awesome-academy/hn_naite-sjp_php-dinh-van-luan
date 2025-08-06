<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Budget;
use App\Enums\HttpStatusCode;
use Illuminate\Validation\ValidationException;
use App\Services\Budget\BudgetService;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use App\Services\Transaction\TransactionService;

class TransactionController extends Controller
{
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
}
