<div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">SIM Card Report</h2>

    <!-- Summary Stats -->
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-gray-600 text-sm">Total SIMs</p>
            <p class="text-2xl font-bold text-blue-600">{{ $data['total_sims'] ?? 0 }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <p class="text-gray-600 text-sm">Active SIMs</p>
            <p class="text-2xl font-bold text-green-600">{{ $data['active_sims'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <p class="text-gray-600 text-sm">Inactive SIMs</p>
            <p class="text-2xl font-bold text-gray-600">{{ $data['inactive_sims'] ?? 0 }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <p class="text-gray-600 text-sm">Total Balance</p>
            <p class="text-2xl font-bold text-purple-600">₦{{ number_format($data['total_balance'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- By Tariff Plan & Status -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">By Tariff Plan</h3>
            <div class="grid grid-cols-2 gap-4">
                @forelse ($data['by_tariff'] ?? [] as $plan => $count)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600 text-sm capitalize">{{ $plan }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $count }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">No data available</p>
                @endforelse
            </div>
        </div>
        <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">By Status</h3>
            <div class="grid grid-cols-2 gap-4">
                @forelse ($data['by_status'] ?? [] as $status => $count)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-600 text-sm capitalize">{{ $status }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $count }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">No data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SIM Cards Table -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">SIM Card Details</h3>
        @if (!empty($data['sim_cards']))
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">SIM Number</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Phone</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Customer</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Balance</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Data</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Tariff</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['sim_cards'] as $sim)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $sim['id'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $sim['sim_number'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $sim['phone_number'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $sim['customer'] ?? 'Unassigned' }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($sim['balance'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ $sim['data_balance'] }} MB</td>
                                <td class="border border-gray-300 px-4 py-2 capitalize">{{ $sim['tariff_plan'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <span class="px-2 py-1 {{ $sim['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded text-sm capitalize">
                                        {{ $sim['status'] }}
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">{{ $sim['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="border border-gray-300 px-4 py-2 text-center text-gray-600">No SIM cards found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No SIM card data available</p>
        @endif
    </div>
</div>

<style>
    .text-2xl {
        font-size: 1.5rem;
    }
    .text-xl {
        font-size: 1.25rem;
    }
    .grid-cols-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .grid-cols-4 {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .gap-4 {
        gap: 1rem;
    }
    .gap-6 {
        gap: 1.5rem;
    }
    table {
        border-collapse: collapse;
    }
</style>
