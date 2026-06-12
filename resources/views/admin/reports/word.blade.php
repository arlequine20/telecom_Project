<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $report->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12pt; color: #222; }
        h1 { font-size: 20pt; margin-bottom: 8px; }
        h2 { font-size: 15pt; margin-top: 24px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 18px; }
        th, td { border: 1px solid #999; padding: 7px; text-align: left; }
        th { background: #f0f0f0; }
        .meta { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>{{ $report->title }}</h1>
    <div class="meta">
        <strong>Type:</strong> {{ $report->getTypeLabel() }}<br>
        <strong>Period:</strong> {{ $report->start_date->format('Y-m-d H:i') }} to {{ $report->end_date->format('Y-m-d H:i') }}<br>
        <strong>Generated:</strong> {{ $report->created_at->format('Y-m-d H:i:s') }}
    </div>

    @php($data = $report->data ?? [])

    @if ($report->type === 'summary')
        <h2>Transactions in Period</h2>
        <table>
            <tr><th>Total Transactions</th><td>{{ $data['summary']['total_transactions'] ?? 0 }}</td></tr>
            <tr><th>Approved Transactions</th><td>{{ $data['summary']['approved_transactions'] ?? 0 }}</td></tr>
            <tr><th>Pending Transactions</th><td>{{ $data['summary']['pending_transactions'] ?? 0 }}</td></tr>
            <tr><th>Cancelled Transactions</th><td>{{ $data['summary']['cancelled_transactions'] ?? 0 }}</td></tr>
            <tr><th>Reversed Transactions</th><td>{{ $data['summary']['reversed_transactions'] ?? 0 }}</td></tr>
            <tr><th>Total Amount Transacted</th><td>RWF {{ number_format($data['summary']['total_amount_transacted'] ?? 0, 2) }}</td></tr>
            <tr><th>Total Fees Collected</th><td>RWF {{ number_format($data['summary']['total_fees_collected'] ?? 0, 2) }}</td></tr>
        </table>

        <h2>Customers</h2>
        <table>
            <tr><th>New Customers in Period</th><td>{{ $data['customers']['new_customers'] ?? 0 }}</td></tr>
            <tr><th>Total Customers</th><td>{{ $data['customers']['total_customers'] ?? 0 }}</td></tr>
            <tr><th>Active Customers Now</th><td>{{ $data['customers']['active_customers'] ?? 0 }}</td></tr>
        </table>

        <h2>SIM Cards</h2>
        <table>
            <tr><th>New SIMs in Period</th><td>{{ $data['sim_cards']['new_sims'] ?? 0 }}</td></tr>
            <tr><th>Total SIMs</th><td>{{ $data['sim_cards']['total_sims'] ?? 0 }}</td></tr>
            <tr><th>Active SIMs Now</th><td>{{ $data['sim_cards']['active_sims'] ?? 0 }}</td></tr>
            <tr><th>Inactive SIMs Now</th><td>{{ $data['sim_cards']['inactive_sims'] ?? 0 }}</td></tr>
            <tr><th>Total Balance</th><td>RWF {{ number_format($data['sim_cards']['total_balance'] ?? 0, 2) }}</td></tr>
            <tr><th>Total Data Balance</th><td>{{ number_format($data['sim_cards']['total_data_balance'] ?? 0, 2) }} MB</td></tr>
        </table>
    @elseif ($report->type === 'transaction')
        <h2>Summary</h2>
        <table>
            <tr><th>Total Transactions</th><td>{{ $data['total_transactions'] ?? 0 }}</td></tr>
            <tr><th>Total Amount</th><td>RWF {{ number_format($data['total_amount'] ?? 0, 2) }}</td></tr>
            <tr><th>Total Fees</th><td>RWF {{ number_format($data['total_fees'] ?? 0, 2) }}</td></tr>
            <tr><th>Average Transaction</th><td>RWF {{ number_format($data['average_transaction'] ?? 0, 2) }}</td></tr>
        </table>
        <h2>Transactions</h2>
        <table>
            <tr><th>ID</th><th>Reference</th><th>From</th><th>To</th><th>Amount</th><th>Fee</th><th>Status</th><th>Date</th></tr>
            @foreach (($data['transactions'] ?? []) as $transaction)
                <tr>
                    <td>{{ $transaction['id'] ?? '' }}</td>
                    <td>{{ $transaction['reference'] ?? '' }}</td>
                    <td>{{ $transaction['from'] ?? '' }}</td>
                    <td>{{ $transaction['to'] ?? '' }}</td>
                    <td>{{ $transaction['amount'] ?? '' }}</td>
                    <td>{{ $transaction['fee'] ?? '' }}</td>
                    <td>{{ $transaction['status'] ?? '' }}</td>
                    <td>{{ $transaction['created_at'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @elseif ($report->type === 'customer')
        <h2>Customers</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>SIMs</th><th>Active SIMs</th><th>Total Balance</th><th>Created</th></tr>
            @foreach (($data['customers'] ?? []) as $customer)
                <tr>
                    <td>{{ $customer['id'] ?? '' }}</td>
                    <td>{{ $customer['name'] ?? '' }}</td>
                    <td>{{ $customer['email'] ?? '' }}</td>
                    <td>{{ $customer['phone'] ?? '' }}</td>
                    <td>{{ $customer['sims_count'] ?? 0 }}</td>
                    <td>{{ $customer['active_sims'] ?? 0 }}</td>
                    <td>{{ $customer['total_balance'] ?? 0 }}</td>
                    <td>{{ $customer['created_at'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @elseif ($report->type === 'sim_card')
        <h2>SIM Cards</h2>
        <table>
            <tr><th>ID</th><th>SIM Number</th><th>Phone Number</th><th>Customer</th><th>Balance</th><th>Data</th><th>Tariff</th><th>Status</th><th>Created</th></tr>
            @foreach (($data['sim_cards'] ?? []) as $sim)
                <tr>
                    <td>{{ $sim['id'] ?? '' }}</td>
                    <td>{{ $sim['sim_number'] ?? '' }}</td>
                    <td>{{ $sim['phone_number'] ?? '' }}</td>
                    <td>{{ $sim['customer'] ?? '' }}</td>
                    <td>{{ $sim['balance'] ?? 0 }}</td>
                    <td>{{ $sim['data_balance'] ?? 0 }}</td>
                    <td>{{ $sim['tariff_plan'] ?? '' }}</td>
                    <td>{{ $sim['status'] ?? '' }}</td>
                    <td>{{ $sim['created_at'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @elseif ($report->type === 'revenue')
        <h2>Revenue</h2>
        <table>
            <tr><th>Total Revenue</th><td>RWF {{ number_format($data['total_revenue'] ?? 0, 2) }}</td></tr>
            <tr><th>Total Fees</th><td>RWF {{ number_format($data['total_fees'] ?? 0, 2) }}</td></tr>
            <tr><th>Net Revenue</th><td>RWF {{ number_format($data['net_revenue'] ?? 0, 2) }}</td></tr>
            <tr><th>Transaction Count</th><td>{{ $data['transaction_count'] ?? 0 }}</td></tr>
            <tr><th>Average Transaction</th><td>RWF {{ number_format($data['average_transaction'] ?? 0, 2) }}</td></tr>
        </table>
    @endif
</body>
</html>
