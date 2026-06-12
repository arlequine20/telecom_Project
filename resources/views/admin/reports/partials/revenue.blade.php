<div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Revenue Report</h2>

    <!-- Summary Stats -->
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <p class="text-gray-600 text-sm">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600">₦{{ number_format($data['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-gray-600 text-sm">Total Fees</p>
            <p class="text-2xl font-bold text-blue-600">₦{{ number_format($data['total_fees'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <p class="text-gray-600 text-sm">Net Revenue</p>
            <p class="text-2xl font-bold text-purple-600">₦{{ number_format($data['net_revenue'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
            <p class="text-gray-600 text-sm">Average Transaction</p>
            <p class="text-2xl font-bold text-indigo-600">₦{{ number_format($data['average_transaction'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Transaction Count -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-8">
        <p class="text-gray-600 text-sm">Total Transactions</p>
        <p class="text-3xl font-bold text-gray-900">{{ $data['transaction_count'] ?? 0 }}</p>
    </div>

    <!-- Daily Revenue Breakdown -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Daily Revenue Breakdown</h3>
        @if (!empty($data['daily_breakdown']))
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Revenue</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Fees</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Net Revenue</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['daily_breakdown'] as $date => $dayData)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $date }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($dayData['total_amount'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($dayData['total_fees'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($dayData['total_amount'] - $dayData['total_fees'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">{{ $dayData['count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="border border-gray-300 px-4 py-2 text-center text-gray-600">No revenue data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No revenue data available</p>
        @endif
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-2 gap-6 bg-gray-50 p-6 rounded-lg border border-gray-200">
        <div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Revenue Metrics</h4>
            <ul class="space-y-2 text-gray-700">
                <li><strong>Total Revenue:</strong> ₦{{ number_format($data['total_revenue'] ?? 0, 2) }}</li>
                <li><strong>Total Fees:</strong> ₦{{ number_format($data['total_fees'] ?? 0, 2) }}</li>
                <li><strong>Net Revenue:</strong> ₦{{ number_format($data['net_revenue'] ?? 0, 2) }}</li>
            </ul>
        </div>
        <div>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Transaction Metrics</h4>
            <ul class="space-y-2 text-gray-700">
                <li><strong>Total Transactions:</strong> {{ $data['transaction_count'] ?? 0 }}</li>
                <li><strong>Average per Transaction:</strong> ₦{{ number_format($data['average_transaction'] ?? 0, 2) }}</li>
                <li><strong>Average Fee per Transaction:</strong> ₦{{ number_format(($data['total_fees'] ?? 0) / max(1, $data['transaction_count'] ?? 0), 2) }}</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .text-3xl {
        font-size: 1.875rem;
    }
    .text-2xl {
        font-size: 1.5rem;
    }
    .text-xl {
        font-size: 1.25rem;
    }
    .text-lg {
        font-size: 1.125rem;
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
