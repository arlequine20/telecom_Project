<div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Customer Report</h2>

    <!-- Summary Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-gray-600 text-sm">Total Customers</p>
            <p class="text-2xl font-bold text-blue-600">{{ $data['total_customers'] ?? 0 }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <p class="text-gray-600 text-sm">Active Customers</p>
            <p class="text-2xl font-bold text-green-600">{{ $data['active_customers'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <p class="text-gray-600 text-sm">Inactive Customers</p>
            <p class="text-2xl font-bold text-gray-600">{{ $data['inactive_customers'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Customer Details</h3>
        @if (!empty($data['customers']))
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Phone</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">SIMs</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Active SIMs</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Total Balance</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['customers'] as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $customer['id'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $customer['name'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $customer['email'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $customer['phone'] }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ $customer['sims_count'] }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">
                                    <span class="px-2 py-1 {{ $customer['active_sims'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded text-sm">
                                        {{ $customer['active_sims'] }}
                                    </span>
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($customer['total_balance'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $customer['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-gray-300 px-4 py-2 text-center text-gray-600">No customers found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No customer data available</p>
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
    .grid-cols-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .gap-4 {
        gap: 1rem;
    }
    table {
        border-collapse: collapse;
    }
</style>
