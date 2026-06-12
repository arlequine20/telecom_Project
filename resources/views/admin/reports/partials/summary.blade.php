<div>
    <h5 class="mb-4">System Summary Report</h5>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="text-muted small">Transactions in Period</div>
                <div class="fs-3 fw-bold">{{ $data['summary']['total_transactions'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="text-muted small">Approved Amount</div>
                <div class="fs-3 fw-bold">RWF {{ number_format($data['summary']['total_amount_transacted'] ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card h-100">
                <div class="text-muted small">Fees Collected</div>
                <div class="fs-3 fw-bold">RWF {{ number_format($data['summary']['total_fees_collected'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <h6 class="mb-3">Transactions in Period</h6>
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr><th>Approved</th><td>{{ $data['summary']['approved_transactions'] ?? 0 }}</td></tr>
                    <tr><th>Pending</th><td>{{ $data['summary']['pending_transactions'] ?? 0 }}</td></tr>
                    <tr><th>Cancelled</th><td>{{ $data['summary']['cancelled_transactions'] ?? 0 }}</td></tr>
                    <tr><th>Reversed</th><td>{{ $data['summary']['reversed_transactions'] ?? 0 }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="col-lg-4">
            <h6 class="mb-3">Customers</h6>
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr><th>New in Period</th><td>{{ $data['customers']['new_customers'] ?? 0 }}</td></tr>
                    <tr><th>Total Customers</th><td>{{ $data['customers']['total_customers'] ?? 0 }}</td></tr>
                    <tr><th>Active Customers Now</th><td>{{ $data['customers']['active_customers'] ?? 0 }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="col-lg-4">
            <h6 class="mb-3">SIM Cards</h6>
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr><th>New in Period</th><td>{{ $data['sim_cards']['new_sims'] ?? 0 }}</td></tr>
                    <tr><th>Total SIMs</th><td>{{ $data['sim_cards']['total_sims'] ?? 0 }}</td></tr>
                    <tr><th>Active SIMs Now</th><td>{{ $data['sim_cards']['active_sims'] ?? 0 }}</td></tr>
                    <tr><th>Inactive SIMs Now</th><td>{{ $data['sim_cards']['inactive_sims'] ?? 0 }}</td></tr>
                    <tr><th>Total Balance</th><td>RWF {{ number_format($data['sim_cards']['total_balance'] ?? 0, 2) }}</td></tr>
                    <tr><th>Total Data Balance</th><td>{{ number_format($data['sim_cards']['total_data_balance'] ?? 0, 2) }} MB</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
