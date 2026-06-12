<?php

namespace App\Services;

use App\Models\Report;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\SimCard;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generate a transaction report
     */
    public function generateTransactionReport(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = Transaction::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $transactions = $query->with(['fromSim.customer', 'toSim.customer'])->get();

        $data = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'total_fees' => $transactions->sum('fee'),
            'average_transaction' => $transactions->count() > 0 ? $transactions->sum('amount') / $transactions->count() : 0,
            'by_status' => $transactions->groupBy('status')->map->count(),
            'transactions' => $transactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'reference' => $t->transaction_reference,
                    'from' => $t->fromSim?->phone_number,
                    'to' => $t->toSim?->phone_number,
                    'amount' => $t->amount,
                    'fee' => $t->fee,
                    'status' => $t->status,
                    'created_at' => $t->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
        ];

        return $data;
    }

    /**
     * Generate a customer report
     */
    public function generateCustomerReport(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = Customer::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->whereHas('user', fn($q) => $q->where('status', $filters['status']));
        }

        $customers = $query->with('simCards')->get();

        $data = [
            'total_customers' => $customers->count(),
            'active_customers' => $customers->filter(fn($c) => $c->simCards()->where('status', 'active')->exists())->count(),
            'inactive_customers' => $customers->filter(fn($c) => !$c->simCards()->where('status', 'active')->exists())->count(),
            'customers' => $customers->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->full_name,
                    'email' => $c->user?->email,
                    'phone' => $c->phone_number,
                    'sims_count' => $c->simCards()->count(),
                    'active_sims' => $c->simCards()->where('status', 'active')->count(),
                    'total_balance' => $c->simCards()->sum('balance'),
                    'created_at' => $c->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
        ];

        return $data;
    }

    /**
     * Generate a SIM card report
     */
    public function generateSimCardReport(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = SimCard::whereBetween('created_at', [$startDate, $endDate]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['tariff_plan'])) {
            $query->where('tariff_plan', $filters['tariff_plan']);
        }

        $simCards = $query->with('customer')->get();

        $data = [
            'total_sims' => $simCards->count(),
            'active_sims' => $simCards->where('status', 'active')->count(),
            'inactive_sims' => $simCards->where('status', 'inactive')->count(),
            'total_balance' => $simCards->sum('balance'),
            'total_data_balance' => $simCards->sum('data_balance'),
            'by_tariff' => $simCards->groupBy('tariff_plan')->map->count(),
            'by_status' => $simCards->groupBy('status')->map->count(),
            'sim_cards' => $simCards->map(function ($s) {
                return [
                    'id' => $s->id,
                    'sim_number' => $s->sim_number,
                    'phone_number' => $s->phone_number,
                    'customer' => $s->customer?->full_name,
                    'balance' => $s->balance,
                    'data_balance' => $s->data_balance,
                    'tariff_plan' => $s->tariff_plan,
                    'status' => $s->status,
                    'created_at' => $s->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray(),
        ];

        return $data;
    }

    /**
     * Generate a revenue report
     */
    public function generateRevenueReport(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $transactions = Transaction::where('status', Transaction::STATUS_APPROVED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $dailyRevenue = $transactions->groupBy(fn($t) => $t->created_at->format('Y-m-d'))
            ->map(function ($group) {
                return [
                    'total_amount' => $group->sum('amount'),
                    'total_fees' => $group->sum('fee'),
                    'count' => $group->count(),
                ];
            });

        $data = [
            'total_revenue' => $transactions->sum('amount'),
            'total_fees' => $transactions->sum('fee'),
            'net_revenue' => $transactions->sum('amount') - $transactions->sum('fee'),
            'transaction_count' => $transactions->count(),
            'average_transaction' => $transactions->count() > 0 ? $transactions->sum('amount') / $transactions->count() : 0,
            'daily_breakdown' => $dailyRevenue->toArray(),
        ];

        return $data;
    }

    /**
     * Generate a system summary report
     */
    public function generateSummaryReport(Carbon $startDate, Carbon $endDate): array
    {
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->get();
        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->get();
        $newSimCards = SimCard::whereBetween('created_at', [$startDate, $endDate])->get();
        $allSimCards = SimCard::all();

        $approvedTransactions = $transactions->where('status', Transaction::STATUS_APPROVED);
        $pendingTransactions = $transactions->where('status', Transaction::STATUS_PENDING);
        $cancelledTransactions = $transactions->where('status', Transaction::STATUS_CANCELLED);
        $reversedTransactions = $transactions->where('status', Transaction::STATUS_REVERSED);

        $data = [
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'summary' => [
                'total_transactions' => $transactions->count(),
                'approved_transactions' => $approvedTransactions->count(),
                'pending_transactions' => $pendingTransactions->count(),
                'cancelled_transactions' => $cancelledTransactions->count(),
                'reversed_transactions' => $reversedTransactions->count(),
                'total_amount_transacted' => $approvedTransactions->sum('amount'),
                'total_fees_collected' => $approvedTransactions->sum('fee'),
            ],
            'customers' => [
                'new_customers' => $newCustomers->count(),
                'total_customers' => Customer::count(),
                'active_customers' => Customer::whereHas('simCards', fn($q) => $q->where('status', 'active'))->count(),
            ],
            'sim_cards' => [
                'new_sims' => $newSimCards->count(),
                'total_sims' => $allSimCards->count(),
                'active_sims' => $allSimCards->where('status', 'active')->count(),
                'inactive_sims' => $allSimCards->where('status', 'inactive')->count(),
                'total_balance' => $allSimCards->sum('balance'),
                'total_data_balance' => $allSimCards->sum('data_balance'),
            ],
        ];

        return $data;
    }
}
