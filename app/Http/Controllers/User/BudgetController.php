<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Constants\RecurringTypes;
use Illuminate\Validation\Rule;
use App\Helpers\ApiResponse;
use App\Models\Budget;
use App\Enums\HttpStatusCode;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Constants\WalletUseScopes;
use App\Services\Budget\BudgetService;

class BudgetController extends Controller
{
    public function create(Request $request, BudgetService $budgetService)
    {
        try {
            $validated = $request->validate([
                'category_id'      => 'required|exists:categories,id',
                'limit_amount'     => 'required|numeric|min:0.01',
                'wallet_use_scope' => ['required', Rule::in(WalletUseScopes::ALL)],
                'wallet_id'        => 'nullable|exists:wallets,id',
                'is_recurring'     => 'required|boolean',
                'recurring_type'   => ['required', Rule::in(RecurringTypes::ALL)],
                'start_date'       => 'required|date',
                'end_date'         => 'required|date|after_or_equal:start_date',
            ]);

            $user = auth()->user();

            $validated['user_id'] = $user->id;

            // Logic check consistency
            if ($validated['wallet_use_scope'] === WalletUseScopes::Wallet && empty($validated['wallet_id'])) {
                return ApiResponse::error(__('budget.wallet_required'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            if ($validated['wallet_use_scope'] === WalletUseScopes::Total) {
                $validated['wallet_id'] = null;
            }

            if ($validated['is_recurring']) {
                if (!in_array($validated['recurring_type'], [
                    RecurringTypes::Weekly,
                    RecurringTypes::Monthly,
                    RecurringTypes::Quarterly,
                    RecurringTypes::Yearly
                ])) {
                    return ApiResponse::error(__('budget.invalid_recurring_type'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                // Calculate the period based on the recurring type
                [$startDate, $endDate] = $this->calculateRecurringDates($validated['recurring_type']);
                $validated['start_date'] = $startDate;
                $validated['end_date'] = $endDate;
            } else {
                // If not recurring -> start/end_date required
                if (empty($validated['start_date']) || empty($validated['end_date'])) {
                    return ApiResponse::error(__('budget.custom_date_required'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }
            }

            // Check if there is already a budget for the same category & time period
            $existingBudget = Budget::where('category_id', $validated['category_id'])
                ->where('wallet_use_scope', $validated['wallet_use_scope'])
                ->where('wallet_id', $validated['wallet_id'])
                ->where('user_id', $validated['user_id'])
                ->whereDate('start_date', $validated['start_date'])
                ->whereDate('end_date', $validated['end_date'])
                ->first();

            // Calculate spent_amount based on existed transactions
            $spentAmount = $budgetService->calculateSpentAmount(
                $user,
                $validated['category_id'],
                $validated['wallet_use_scope'],
                $validated['wallet_id'],
                $validated['start_date'],
                $validated['end_date']
            );

            if ($existingBudget) {
                $existingBudget->update([
                    'limit_amount' => $validated['limit_amount'],
                    'spent_amount' => $spentAmount,
                    'is_recurring' => $validated['is_recurring'],
                    'recurring_type' => $validated['recurring_type'],
                ]);

                $budget = $existingBudget;
            } else {
                $validated['spent_amount'] = $spentAmount;
                $budget = Budget::create($validated);
            }

            return ApiResponse::success([
                'budget' => $budget
            ], __('budget.created_successfully'), HttpStatusCode::CREATED);

        } catch (ValidationException $e) {
            return ApiResponse::error(__('budget.invalid_data'), $e->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return ApiResponse::error(__('budget.created_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Calculate start time -> end time based on recurring type
     */
    private function calculateRecurringDates(string $recurringType): array
    {
        $now = Carbon::now();

        return match ($recurringType) {
            RecurringTypes::Weekly => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek()
            ],
            RecurringTypes::Monthly => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ],
            RecurringTypes::Quarterly => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter()
            ],
            RecurringTypes::Yearly => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear()
            ],
            default => [null, null],
        };
    }
}
