<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('service-package.service_package_management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <form method="GET" action="{{ route('service-packages.index') }}"
                        class="flex items-center mb-4 space-x-4">
                        <input type="text" name="search" placeholder="Search title or description"
                            value="{{ request('search') }}" class="border rounded px-3 py-2 text-sm w-1/3" />

                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">
                            {{ __('Search') }}
                        </button>
                    </form>

                    <x-flash-message />

                    <table class="min-w-full divide-y divide-gray-200 mt-4">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('service-package.id') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('service-package.title') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('service-package.description') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('service-package.price') }}
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('service-package.action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($packages as $package)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $package->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $package->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $package->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($package->price) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm flex space-x-4">
                                        <a href="{{ route('service-packages.show', $package->id) }}"
                                            class="text-green-600 hover:text-green-900" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="#" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-pen"></i></a>

                                        <a href="#" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        {{ __('service-package.no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $packages->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
