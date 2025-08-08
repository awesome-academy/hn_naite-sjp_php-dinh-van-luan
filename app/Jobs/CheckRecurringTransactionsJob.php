<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RecurringTransaction;
use App\Services\Transaction\TransactionService;
use App\Services\Budget\BudgetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckRecurringTransactionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(TransactionService $transactionService, BudgetService $budgetService)
    {
        $today = now()->startOfDay();

        $recurrings = RecurringTransaction::where('start_date', '<=', $today)
            ->get();

        foreach ($recurrings as $recurring) {
            // Skip if past end date or exceeded max occurrences
            if (!$recurring->is_forever) {
                if ($recurring->end_date && $today->gt($recurring->end_date)) {
                    continue;
                }
                if ($recurring->max_occurrences && $recurring->transactions()->count() >= $recurring->max_occurrences) {
                    continue;
                }
            }

            // Loop from start_date up to today, incrementing by recurrence interval
            $occurrenceDate = $recurring->start_date->copy()->startOfDay();

            while ($occurrenceDate->lte($today)) {
                if ($occurrenceDate->equalTo($today)) {
                    // Check if a transaction has already been created today
                    $exists = $recurring->transactions()
                        ->whereDate('date', $today)
                        ->exists();

                    if (!$exists) {
                        DB::beginTransaction();
                        try {
                            $transaction = $transactionService->createFromRecurring($recurring);

                            $user = $transaction->wallet->user;

                            $budgetService->updateBudgetsAfterTransaction($transaction, $user);

                            DB::commit();
                        } catch (\Throwable $e) {
                            DB::rollBack();

                            Log::error('Failed to create transaction for recurring ID ' . $recurring->id, [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }

                    break;
                }

                // Increment the date by the recurrence interval
                $occurrenceDate = match ($recurring->recurring_type) {
                    'daily' => $occurrenceDate->addDays($recurring->interval_value),
                    'weekly' => $occurrenceDate->addWeeks($recurring->interval_value),
                    'monthly' => $occurrenceDate->addMonths($recurring->interval_value),
                    'yearly' => $occurrenceDate->addYears($recurring->interval_value),
                };
            }
        }
    }
}
