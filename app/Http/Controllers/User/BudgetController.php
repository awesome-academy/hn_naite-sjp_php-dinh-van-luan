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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Constants\UserRoles;

class BudgetController extends Controller
{
    protected int $perPage;

    public function __construct()
    {
        $this->perPage = config("paginate")["per_page"] ?? 10;
    }

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
                if (!$user->hasRole(UserRoles::PREMIUM_USER)) {
                    return ApiResponse::error(__('budget.premium_user'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                if (!in_array($validated['recurring_type'], [
                    RecurringTypes::Weekly,
                    RecurringTypes::Monthly,
                    RecurringTypes::Quarterly,
                    RecurringTypes::Yearly
                ])) {
                    return ApiResponse::error(__('budget.invalid_recurring_type'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                $timezone = $user?->timezone ?? config('app.timezone');

                // Calculate the period based on the recurring type
                [$startDate, $endDate] = $this->calculateRecurringDates($validated['recurring_type'], $timezone);
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
            Log::error('Budget update failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('budget.created_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Calculate start time -> end time based on recurring type
     */
    private function calculateRecurringDates(string $recurringType, ?string $timezone = null): array
    {
        $now = $timezone ? Carbon::now($timezone) : Carbon::now();

        [$start, $end] = match ($recurringType) {
            RecurringTypes::Weekly    => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            RecurringTypes::Monthly   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            RecurringTypes::Quarterly => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            RecurringTypes::Yearly    => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default                   => [null, null],
        };

        return [
            $start?->format('Y-m-d'),
            $end?->format('Y-m-d'),
        ];
    }

    public function getBudgetByUser(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->only([
                'wallet_use_scope', 'wallet_id', 'category_id',
                'is_recurring', 'recurring_type',
                'start_date', 'end_date',
                'limit_amount_from', 'limit_amount_to',
                'spent_amount_from', 'spent_amount_to',
                'per_page', 'page'
            ]);

            $diff = array_diff(array_keys($request->all()), array_keys($validatedData));

            if (!empty($diff)) {
                return ApiResponse::error(__('budget.invalid_query'), [
                    'invalid_keys' => $diff
                ], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $validator = Validator::make($validatedData, [
                'wallet_use_scope'     => ['nullable', Rule::in(WalletUseScopes::ALL)],
                'wallet_id'            => 'required_if:wallet_use_scope,wallet|integer|exists:wallets,id',
                'category_id'          => 'nullable|integer|exists:categories,id',
                'is_recurring'         => 'nullable|boolean',
                'recurring_type'       => ['nullable', Rule::in(RecurringTypes::ALL)],
                'start_date'           => 'nullable|date',
                'end_date'             => 'nullable|date|after_or_equal:start_date',
                'limit_amount_from'    => 'nullable|numeric|min:0',
                'limit_amount_to'      => 'nullable|numeric|gte:limit_amount_from',
                'spent_amount_from'    => 'nullable|numeric|min:0',
                'spent_amount_to'      => 'nullable|numeric|gte:spent_amount_from',
                'per_page'             => 'nullable|integer|min:1|max:100',
                'page'                 => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error(
                    __('budget.invalid_query'),
                    $validator->errors(),
                    HttpStatusCode::UNPROCESSABLE_ENTITY
                );
            }

            $perPage = $request->query('per_page', $this->perPage);

            $cacheKey = 'budgets:user:' . $user->id . ':filters:' . md5(json_encode($validatedData) . $perPage);

            $budgets = Cache::remember($cacheKey, now()->addMinutes(value: 2), function () use ($user, $request, $perPage) {
                return Budget::query()
                    ->where('user_id', $user->id)
                    ->when($request->wallet_use_scope, fn ($q) => $q->where('wallet_use_scope', $request->wallet_use_scope))
                    ->when($request->wallet_use_scope === 'wallet' && $request->wallet_id, fn ($q) => $q->where('wallet_id', $request->wallet_id))
                    ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
                    ->when(!is_null($request->is_recurring), fn ($q) => $q->where('is_recurring', $request->is_recurring))
                    ->when($request->recurring_type, fn ($q) => $q->where('recurring_type', $request->recurring_type))
                    ->when($request->start_date, fn ($q) => $q->whereDate('start_date', '>=', $request->start_date))
                    ->when($request->end_date, fn ($q) => $q->whereDate('end_date', '<=', $request->end_date))
                    ->when($request->limit_amount_from, fn ($q) => $q->where('limit_amount', '>=', $request->limit_amount_from))
                    ->when($request->limit_amount_to, fn ($q) => $q->where('limit_amount', '<=', $request->limit_amount_to))
                    ->when($request->spent_amount_from, fn ($q) => $q->where('spent_amount', '>=', $request->spent_amount_from))
                    ->when($request->spent_amount_to, fn ($q) => $q->where('spent_amount', '<=', $request->spent_amount_to))
                    ->with(['category'])
                    ->paginate($perPage);
            });

            return ApiResponse::success([
                'budgets' => $budgets
            ], HttpStatusCode::OK);
        } catch (\Throwable $e) {
            Log::error('Budget update failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(
                __('budget.fetch_failed'),
                [],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getDetailById(Request $request, $id)
    {
        try {
            $user = $request->user();

            $budget = Budget::where('id', $id)
                            ->where('user_id', $user->id)
                            ->with(['category'])
                            ->first();

            if (!$budget) {
                return ApiResponse::success([], __('budget.budget_not_found'), HttpStatusCode::NOT_FOUND);
            }

            return ApiResponse::success(['budget' => $budget], __('budget.budget_detail_retrieved'), HttpStatusCode::NOT_FOUND);
        } catch (\Throwable $e) {
            Log::error('Budget update failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('budget.error_getting_budget_detail'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, BudgetService $budgetService, int $id)
    {
        try {
            $user = auth()->user();

            $budget = Budget::find($id);

            if (!$budget) {
                return ApiResponse::error(__('budget.budget_not_found'), [], HttpStatusCode::NOT_FOUND);
            }

            if ($budget->user_id !== $user->id) {
                return ApiResponse::error(__('budget.unauthorized'), [], HttpStatusCode::FORBIDDEN);
            }

            $validated = $request->validate([
                'category_id'      => 'sometimes|required|exists:categories,id',
                'limit_amount'     => 'sometimes|required|numeric|min:0.01',
                'wallet_use_scope' => ['sometimes', Rule::in(WalletUseScopes::ALL)],
                'wallet_id'        => 'nullable|exists:wallets,id',
                'is_recurring'     => 'sometimes|required|boolean',
                'recurring_type'   => ['sometimes', Rule::in(RecurringTypes::ALL)],
                'start_date'       => 'nullable|date',
                'end_date'         => 'nullable|date|after_or_equal:start_date',
            ]);

            if (
                ($validated['wallet_use_scope'] ?? $budget->wallet_use_scope) === WalletUseScopes::Wallet &&
                empty($validated['wallet_id'] ?? $budget->wallet_id)
            ) {
                return ApiResponse::error(__('budget.wallet_required'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            if (($validated['wallet_use_scope'] ?? $budget->wallet_use_scope) === WalletUseScopes::Total) {
                $validated['wallet_id'] = null;
            }

            if ($validated['is_recurring'] ?? $budget->is_recurring) {
                $recurringType = $validated['recurring_type'] ?? $budget->recurring_type;

                if (!in_array($recurringType, RecurringTypes::ALL)) {
                    return ApiResponse::error(__('budget.invalid_recurring_type'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }

                [$startDate, $endDate] = $this->calculateRecurringDates($recurringType, $user?->timezone);
                $validated['start_date'] = $startDate;
                $validated['end_date'] = $endDate;
            } else {
                if (empty($validated['start_date']) && empty($budget->start_date)) {
                    return ApiResponse::error(__('budget.custom_date_required'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
                }
            }

            // check exist budget
            $categoryId     = $validated['category_id'] ?? $budget->category_id;
            $walletScope    = $validated['wallet_use_scope'] ?? $budget->wallet_use_scope;
            $walletId       = $validated['wallet_id'] ?? $budget->wallet_id;
            $startDate      = $validated['start_date'] ?? $budget->start_date;
            $endDate        = $validated['end_date'] ?? $budget->end_date;

            $duplicate = Budget::where('user_id', $user->id)
                        ->where('id', '!=', $budget->id)
                        ->where('category_id', $categoryId)
                        ->where('wallet_use_scope', $walletScope)
                        ->when($walletScope === WalletUseScopes::Wallet, fn ($q) => $q->where('wallet_id', $walletId))
                        ->when($walletScope === WalletUseScopes::Total, fn ($q) => $q->whereNull('wallet_id'))
                        ->whereDate('start_date', $startDate)
                        ->whereDate('end_date', $endDate)
                        ->exists();

            if ($duplicate) {
                return ApiResponse::error(__('budget.duplicate_budget'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
            }

            $spentAmount = $budgetService->calculateSpentAmount(
                $user,
                $categoryId,
                $walletScope,
                $walletId,
                $startDate,
                $endDate
            );

            $validated['spent_amount'] = $spentAmount;

            $budget->update($validated);

            return ApiResponse::success([
                'budget' => $budget
            ], __('budget.updated_successfully'), HttpStatusCode::OK);

        } catch (ValidationException $e) {
            return ApiResponse::error(__('budget.invalid_data'), $e->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Budget update failed', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return ApiResponse::error(__('budget.updated_failed'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}
