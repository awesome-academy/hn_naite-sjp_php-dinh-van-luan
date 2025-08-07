<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('service-package.service_package_detail') }}
        </h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-100 px-6 py-4 border-b">
                <h3 class="text-lg font-bold text-gray-700"> {{ __('service-package.service_package_detail') }}</h3>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('service-package.id') }}</p>
                        <p class="text-base font-semibold text-gray-800">{{ $package->id }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ __('service-package.title') }}</p>
                        <p class="text-base font-semibold text-gray-800">{{ $package->title }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ __('service-package.description') }}</p>
                        <p class="text-base text-gray-700">{{ $package->description }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ __('service-package.price') }}</p>
                        <p class="text-base font-semibold text-gray-800">
                            {{ number_format($package->price, 0, ',', '.') }} VND</p>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="px-6 pt-8 pb-6">
                <h4 class="text-md font-semibold text-gray-700 mb-4">{{ __('service-package.register_users') }}</h4>

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.user_id') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.username') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.email') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.register_date') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.expire_date') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.payment_method') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.amount') }}</th>
                            <th class="px-4 py-2 text-left"> {{ __('service-package.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($package->userServicePackages as $usp)
                            <tr>
                                <td class="px-4 py-2">{{ $usp->user->id }}</td>
                                <td class="px-4 py-2">{{ $usp->user->name }}</td>
                                <td class="px-4 py-2">{{ $usp->user->email }}</td>
                                <td class="px-4 py-2">{{ $usp->register_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $usp->expire_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 capitalize">{{ $usp->payment_method }}</td>
                                <td class="px-4 py-2">{{ number_format($usp->amount, 0, ',', '.') }} VND</td>
                                <td class="px-4 py-2 capitalize">{{ $usp->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-3 text-center text-gray-500 italic">
                                    {{ __('service-package.no_users') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t flex justify-end">
                <a href="{{ route('service-packages.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
