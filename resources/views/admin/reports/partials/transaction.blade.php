<div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Transaction Report</h2>

    <!-- Summary Stats -->
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-gray-600 text-sm">Total Transactions</p>
            <p class="text-2xl font-bold text-blue-600">{{ $data['total_transactions'] ?? 0 }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <p class="text-gray-600 text-sm">Total Amount</p>
            <p class="text-2xl font-bold text-green-600">₦{{ number_format($data['total_amount'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <p class="text-gray-600 text-sm">Total Fees</p>
            <p class="text-2xl font-bold text-purple-600">₦{{ number_format($data['total_fees'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
            <p class="text-gray-600 text-sm">Average Transaction</p>
            <p class="text-2xl font-bold text-indigo-600">₦{{ number_format($data['average_transaction'] ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- By Status -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Transactions by Status</h3>
        <div class="grid grid-cols-2 gap-4">
            @forelse ($data['by_status'] ?? [] as $status => $count)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-600 text-sm capitalize">{{ $status }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $count }}</p>
                </div>
            @empty
                <p class="text-gray-600">No transaction data available</p>
            @endforelse
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Transaction Details</h3>
        @if (!empty($data['transactions']))
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Reference</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">From</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">To</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Amount</th>
                            <th class="border border-gray-300 px-4 py-2 text-right">Fee</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['transactions'] as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $transaction['id'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $transaction['reference'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $transaction['from'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $transaction['to'] }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($transaction['amount'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-right">₦{{ number_format($transaction['fee'], 2) }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-sm capitalize">{{ $transaction['status'] }}</span>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">{{ $transaction['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-gray-300 px-4 py-2 text-center text-gray-600">No transactions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-600">No transaction data available</p>
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
    table {
        border-collapse: collapse;
    }
</style>
