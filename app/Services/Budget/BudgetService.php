<?php

namespace App\Services\Budget;

use App\Constants\WalletUseScopes;
use App\Models\ExchangeRate;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;

class BudgetService
{
    public function calculateSpentAmount($user, $categoryId, $walletScope, $walletId, $startDate, $endDate): float
    {
        $toCurrency = null;
        if ($walletScope === WalletUseScopes::Total) {
            $toCurrency = optional($user?->userSetting)?->currency?->code;
        } else {
            $wallet = Wallet::with('currency')->find($walletId);
            $toCurrency = optional($wallet?->currency)?->code;
        }

        if (!$toCurrency) {
            throw new \RuntimeException(__('wallet.user_setting_currency_missing'));
        }

        $today = Carbon::today()->format('Y-m-d');
        $total = 0;

        // Case 1: WalletUseScopes::Wallet
        // - Only one wallet is used -> only one currency involved.
        // - So we SUM all transactions in SQL grouped by its currency.
        // - Then fetch exchange rates once and convert the total amount to target currency.

        // Case 2: WalletUseScopes::Total
        // - Multiple wallets may involve multiple currencies.
        // - To optimize performance:
        //     Step 1: Use SQL GROUP BY to calculate total amount for each currency directly in DB.
        //     Step 2: Fetch all needed exchange rates at once.
        //     Step 3: Convert each grouped total (per currency) to the target currency and sum them all.


        // Case 1: Wallet
        if ($walletScope === WalletUseScopes::Wallet) {
            $walletCurrency = Wallet::with('currency')->find($walletId)?->currency?->code;

            $rateFrom = $this->getExchangeRate($walletCurrency, $today);
            $rateTo   = $this->getExchangeRate($toCurrency, $today);

            $sumAmount = Transaction::where('category_id', $categoryId)
            ->where('wallet_id', $walletId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

            $total = ($walletCurrency !== $toCurrency)
                ? $sumAmount * ($rateTo / $rateFrom)
                : $sumAmount;

        } else {
            // Case 2: Total
            // Perform SQL grouping by currency
            $groupedAmounts = Transaction::selectRaw('currencies.code as currency_code, SUM(transactions.amount) as total')
                                        ->join('wallets', 'wallets.id', '=', 'transactions.wallet_id')
                                        ->join('currencies', 'currencies.id', '=', 'wallets.currency_id')
                                        ->where('transactions.category_id', $categoryId)
                                        ->whereIn('transactions.wallet_id', $user->wallets->pluck('id'))
                                        ->whereBetween('transactions.date', [$startDate, $endDate])
                                        ->groupBy('currencies.code')
                                        ->get();

            foreach ($groupedAmounts as $group) {
                $currencyCode = $group->currency_code;
                $amount       = $group->total;

                $rateFrom = $this->getExchangeRate($currencyCode, $today);
                $rateTo   = $this->getExchangeRate($toCurrency, $today);

                $total += ($currencyCode !== $toCurrency)
                    ? $amount * ($rateTo / $rateFrom)
                    : $amount;
            }
        }

        return round($total, 2);
    }

    /**
     * Return exchange rate for a given currency code and date
     */
    private function getExchangeRate(?string $currencyCode, string $date): float
    {
        if (!$currencyCode) {
            return 1;
        }

        return ExchangeRate::where('target_currency_code', $currencyCode)
            ->where('date', $date)
            ->value('rate') ?? 1;
    }
}
