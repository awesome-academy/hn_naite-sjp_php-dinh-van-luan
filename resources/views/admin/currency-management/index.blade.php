<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('currency.currency_management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Search & Filter -->
                    <form method="GET" action="{{ route('currencies.index') }}" class="mb-4 flex items-center space-x-4">
                        <input type="text" name="search"
                            placeholder="{{ __('currency.search_currency_placeholder') }}"
                            value="{{ request('search') }}"
                            class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300">

                        <input type="date" name="date" value="{{ $selectedDate }}"
                            class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring focus:border-blue-300">

                        <button type="submit"
                            class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">{{ __('Search') }}</button>

                        <a href="{{ route('currencies.index') }}"
                            class="text-sm text-gray-500 ml-2">{{ __('currency.reset') }}</a>
                    </form>

                    <x-flash-message />

                    <div class="flex justify-end mb-4">
                        <form action="{{ route('currencies.updateExchangeRates') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">{{ __('currency.update_exchange_rate') }}</button>
                        </form>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('ID') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('currency.currency_name') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('currency.code') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('currency.symbol') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('currency.exchange_rate') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('currency.is_default') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($currencies as $currency)
                                @php
                                    $rateForDate = $currency->exchangeRates->first();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $currency->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $currency->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $currency->code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $currency->symbol }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $rateForDate ? $rateForDate->rate : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <form action="{{ route('currencies.changeCurrencyDefault', $currency->id) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" onchange="this.form.submit()"
                                                    class="sr-only peer"
                                                    {{ $rateForDate?->currency_id === $currency->id ? 'checked disabled' : '' }}>
                                                <div
                                                    class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 
                                                            peer-checked:bg-blue-600 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed 
                                                            after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white 
                                                            after:border after:rounded-full after:h-5 after:w-5 after:transition-all 
                                                            peer-checked:after:translate-x-full peer-checked:after:border-white relative">
                                                </div>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm flex space-x-4">
                                        <a href="#" class="text-green-600 hover:text-green-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="#" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        <form action="#" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this currency?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                        {{ __('No data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $currencies->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
