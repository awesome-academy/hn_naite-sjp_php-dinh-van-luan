<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Budget\BudgetService;
use App\Constants\RecurringTypes;
use InvalidArgumentException;
use Illuminate\Bus\Queueable;
use App\Models\Budget;
use Carbon\Carbon;

class AutoCreateRecurringBudgetJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $recurringBudgets = Budget::with('user')
                                    ->where('is_recurring', true)
                                    ->get();

        foreach ($recurringBudgets as $budget) {
            [$newStart, $newEnd] = $this->getNextPeriod($budget->recurring_type, $budget->start_date);

            // Avoid duplicate creation
            $alreadyExists = Budget::where('user_id', $budget->user_id)
                ->where('category_id', $budget->category_id)
                ->where('wallet_use_scope', $budget->wallet_use_scope)
                ->where('wallet_id', $budget->wallet_id)
                ->whereDate('start_date', $newStart)
                ->whereDate('end_date', $newEnd)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $spentAmount = app(BudgetService::class)->calculateSpentAmount(
                $budget->user,
                $budget->category_id,
                $budget->wallet_use_scope,
                $budget->wallet_id,
                $newStart,
                $newEnd
            );

            // Create new budget instance
            Budget::create([
                'user_id'         => $budget->user_id,
                'category_id'     => $budget->category_id,
                'limit_amount'    => $budget->limit_amount,
                'spent_amount'    => $spentAmount,
                'wallet_use_scope' => $budget->wallet_use_scope,
                'wallet_id'       => $budget->wallet_id,
                'is_recurring'    => false,
                'recurring_type'  => $budget->recurring_type,
                'start_date'      => $newStart,
                'end_date'        => $newEnd,
            ]);
        }
    }

    private function getNextPeriod(string $type, string $lastStartDate): array
    {
        $start = Carbon::parse($lastStartDate);

        return match ($type) {
            RecurringTypes::Weekly    => [$start->copy()->addWeek()->startOfWeek(), $start->copy()->addWeek()->endOfWeek()],
            RecurringTypes::Monthly   => [$start->copy()->addMonth()->startOfMonth(), $start->copy()->addMonth()->endOfMonth()],
            RecurringTypes::Quarterly => [$start->copy()->addQuarter()->startOfQuarter(), $start->copy()->addQuarter()->endOfQuarter()],
            RecurringTypes::Yearly    => [$start->copy()->addYear()->startOfYear(), $start->copy()->addYear()->endOfYear()],
            default                   => throw new InvalidArgumentException("Invalid recurring type: $type"),
        };
    }
}
