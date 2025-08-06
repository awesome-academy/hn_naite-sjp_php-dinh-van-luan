<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mavinoo\Batch\Batch;

class CurrencyManagementController extends Controller
{
    protected int $perPage;

    public function __construct()
    {
        $this->perPage = config("paginate")["per_page"] ?? 10;
    }

    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $search = $request->input('search');

        $currenciesQuery = Currency::with(['exchangeRates' => function ($query) use ($selectedDate) {
            $query->where('date', $selectedDate);
        }]);

        if ($search) {
            $currenciesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $currencies = $currenciesQuery->orderBy('id')->paginate($this->perPage);

        return view('admin.currency-management.index', [
            'currencies' => $currencies,
            'selectedDate' => $selectedDate,
            'search' => $search,
        ]);
    }

    public function updateExchangeRates(ExchangeRateService $service)
    {
        try {
            $success = $service->updateRates();

            if ($success) {
                return redirect()->back()->with('success', __('currency.update_exchange_rates_success'));
            }

            return redirect()->back()->with('error', __('currency.update_exchange_rates_error'));
        } catch (\Throwable $e) {
            Log::error('Unexpected error while updating exchange rates', [
                'message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', __('currency.update_exchange_rates_exception'));
        }
    }

    public function changeCurrencyDefault(Currency $currency, ExchangeRateService $service)
    {
        DB::beginTransaction();
        try {
            Currency::where('is_default', true)->update(['is_default' => false]);

            $currency->update(['is_default' => true]);

            $success = $this->updateRates($currency);

            if (!$success) {
                return redirect()->back()->with('error', __('currency.update_exchange_rates_error'));
            }

            DB::commit();

            return redirect()->back()->with('success', __('currency.set_default_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to set default currency.', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', __('currency.set_default_error'));
        }
    }

    public function updateRates(Currency $newCurrencyDefault): bool
    {
        try {
            $batch = app(Batch::class);

            $dates = ExchangeRate::select('date')->distinct()->pluck('date');

            foreach ($dates as $date) {
                // Get exchangeRates by date
                $exchangeRateByDates = ExchangeRate::where('date', $date)->get();

                $newExchangeRateByDate = $exchangeRateByDates->firstWhere('target_currency_code', $newCurrencyDefault->code)?->rate;

                if (!$newExchangeRateByDate) {
                    Log::warning("Missing exchange rate for {$newCurrencyDefault->code} on {$date}");
                    continue;
                }

                //which currency want to switch to default:
                //exchange rate of that currency: divide by itself
                //other currencies: get current rate / rate of that new currency default
                $updateData = $exchangeRateByDates->map(function ($record) use ($newExchangeRateByDate, $newCurrencyDefault) {
                    return [
                        'id' => $record->id,
                        'currency_id' => $newCurrencyDefault->id,
                        'rate' => $record->rate / $newExchangeRateByDate,
                    ];
                })->toArray();

                $batch->update(new ExchangeRate(), $updateData, 'id');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set default currency.', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
