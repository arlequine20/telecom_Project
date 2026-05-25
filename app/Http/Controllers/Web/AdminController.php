<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SimCard;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->isAdmin()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $totalCustomers = Customer::count();
        $activeSims = SimCard::where('status', 'active')->count();
        $totalBalance = SimCard::sum('balance');
        $pendingTransactions = Transaction::whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_REVERSAL_REQUESTED])->count();
        $recentTransactions = Transaction::with(['fromSim', 'toSim'])->latest()->take(10)->get();
        
        return view('admin.dashboard', compact(
            'totalCustomers', 'activeSims', 'totalBalance', 
            'pendingTransactions', 'recentTransactions'
        ));
    }
    
    public function customers()
    {
        $customers = Customer::with('simCards')->paginate(20);
        return view('admin.customers', compact('customers'));
    }
    
    public function simCards()
    {
        $simCards = SimCard::with('customer')->paginate(20);
        return view('admin.sim-cards', compact('simCards'));
    }

    public function showBuyDataForm(SimCard $sim)
    {
        return view('admin.buy-data', compact('sim'));
    }

    public function purchaseData(Request $request, SimCard $sim)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $wallet = $request->user()->wallet;
        if (!$wallet) {
            $wallet = $request->user()->wallet()->create([
                'balance' => 0,
                'total_spend' => 0,
                'data_balance' => 0,
                'data_unit' => 'MB',
            ]);
        }

        if (!$wallet->deductBalance($validated['amount'])) {
            return back()->with('error', 'Insufficient wallet balance to buy data.');
        }

        DB::transaction(function () use ($sim, $validated) {
            $sim->addDataBalance($validated['amount']);
            Transaction::create([
                'from_sim_id' => $sim->id,
                'to_sim_id' => $sim->id,
                'amount' => $validated['amount'],
                'fee' => 0,
                'status' => Transaction::STATUS_APPROVED,
                'description' => 'Admin data purchase: ' . $validated['amount'] . ' MB',
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('admin.sim-cards')->with('success', 'Data purchased successfully for SIM ' . $sim->sim_number . '.');
    }

    public function showCreateForm()
    {
        return view('admin.create-sim-card');
    }

    public function storeSimCard(Request $request)
    {
        $validated = $request->validate([
            'sim_number' => 'required|string|unique:sim_cards,sim_number',
            'phone_number' => 'required|string|unique:sim_cards,phone_number',
            'tariff_plan' => 'required|in:prepaid,postpaid',
        ]);

        SimCard::create([
            'sim_number' => $validated['sim_number'],
            'phone_number' => $validated['phone_number'],
            'balance' => 0,
            'tariff_plan' => $validated['tariff_plan'],
            'status' => 'inactive',
        ]);

        return redirect()->route('admin.sim-cards')->with('success', 'SIM card created successfully.');
    }

    public function showAssignForm(SimCard $sim)
    {
        $customers = Customer::orderBy('first_name')->get();
        return view('admin.assign-sim', compact('sim', 'customers'));
    }

    public function assignSimCard(Request $request, SimCard $sim)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $wasAssigned = $sim->customer_id !== null;

        $sim->customer_id = $customer->id;
        $sim->status = 'active';
        $sim->assigned_at = $sim->assigned_at ?? now();
        $sim->last_activity_at = now();
        $sim->save();

        $message = $wasAssigned
            ? 'SIM card assignment updated to ' . $customer->full_name . '.'
            : 'SIM card assigned to ' . $customer->full_name . '.';

        return redirect()->route('admin.sim-cards')->with('success', $message);
    }
    
    public function pendingTransactions()
    {
        $transactions = Transaction::with(['fromSim', 'toSim'])
            ->whereIn('status', [Transaction::STATUS_PENDING, Transaction::STATUS_REVERSAL_REQUESTED])
            ->latest()
            ->paginate(20);
        return view('admin.pending', compact('transactions'));
    }

    public function approveTransaction(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_PENDING) {
            DB::transaction(function () use ($transaction) {
                $transaction->approve();
            });

            return response()->json(['success' => true, 'message' => 'Transaction approved successfully.']);
        }

        if ($transaction->status === Transaction::STATUS_REVERSAL_REQUESTED) {
            $receiver = $transaction->toSim;
            $sender = $transaction->fromSim;
            $amount = $transaction->amount;

            if ($receiver->balance < $amount) {
                return response()->json(['success' => false, 'message' => 'Cannot reverse transaction because recipient has insufficient balance.']);
            }

            DB::transaction(function () use ($receiver, $sender, $transaction, $amount) {
                $receiver->balance -= $amount;
                $receiver->save();

                $sender->balance += $amount;
                $sender->save();

                $transaction->status = Transaction::STATUS_REVERSED;
                $transaction->approved_at = now();
                $transaction->save();
            });

            return response()->json(['success' => true, 'message' => 'Reversal approved and funds returned to sender.']);
        }

        return response()->json(['success' => false, 'message' => 'Only pending or reversal requests can be approved.']);
    }

    public function cancelTransaction(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_PENDING) {
            $transaction->status = Transaction::STATUS_CANCELLED;
            $transaction->save();
            return response()->json(['success' => true, 'message' => 'Transaction cancelled successfully.']);
        }

        if ($transaction->status === Transaction::STATUS_REVERSAL_REQUESTED) {
            $transaction->status = Transaction::STATUS_REVERSAL_DENIED;
            $transaction->save();
            return response()->json(['success' => true, 'message' => 'Reversal request denied successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Only pending or reversal requests can be cancelled or denied.']);
    }
    
    public function transactionHistory()
    {
        $transactions = Transaction::with(['fromSim', 'toSim'])
            ->latest()
            ->paginate(30);
        return view('admin.history', compact('transactions'));
    }

    public function apiChecker()
    {
        $endpoints = [
            [
                'group' => 'Authentication',
                'items' => [
                    [
                        'name' => 'Register user',
                        'method' => 'POST',
                        'path' => '/auth/register',
                        'auth' => false,
                        'description' => 'Create a user or admin account and return an API token.',
                        'body' => [
                            'name' => 'API Test User',
                            'email' => 'api.user@example.com',
                            'password' => 'password',
                            'password_confirmation' => 'password',
                            'role' => 'user',
                        ],
                    ],
                    [
                        'name' => 'Login',
                        'method' => 'POST',
                        'path' => '/auth/login',
                        'auth' => false,
                        'description' => 'Login with email and password to receive a bearer token.',
                        'body' => [
                            'email' => auth()->user()->email ?? 'admin@example.com',
                            'password' => 'password',
                        ],
                    ],
                    [
                        'name' => 'Current user',
                        'method' => 'GET',
                        'path' => '/auth/me',
                        'auth' => true,
                        'description' => 'Return the authenticated user, wallet, customer, and SIM cards.',
                    ],
                    [
                        'name' => 'Logout',
                        'method' => 'POST',
                        'path' => '/auth/logout',
                        'auth' => true,
                        'description' => 'Revoke the current user tokens.',
                    ],
                ],
            ],
            [
                'group' => 'Customers',
                'items' => [
                    ['name' => 'List customers', 'method' => 'GET', 'path' => '/customers', 'auth' => true, 'description' => 'Admin-only paginated customer list.'],
                    ['name' => 'Customer stats', 'method' => 'GET', 'path' => '/customers/stats/overview', 'auth' => true, 'description' => 'Admin-only customer totals.'],
                    ['name' => 'Show customer', 'method' => 'GET', 'path' => '/customers/1', 'auth' => true, 'description' => 'Get a single customer by ID.'],
                    [
                        'name' => 'Create customer',
                        'method' => 'POST',
                        'path' => '/customers',
                        'auth' => true,
                        'description' => 'Admin-only customer creation.',
                        'body' => [
                            'first_name' => 'Test',
                            'last_name' => 'Customer',
                            'email' => 'customer@example.com',
                            'phone' => '+250780000000',
                            'address' => 'Kigali',
                            'national_id' => '1199000000000000',
                            'date_of_birth' => '1990-01-01',
                        ],
                    ],
                ],
            ],
            [
                'group' => 'SIM Cards',
                'items' => [
                    ['name' => 'Lookup by phone', 'method' => 'GET', 'path' => '/sim-cards/lookup/by-phone/+250780000000', 'auth' => false, 'description' => 'Public phone lookup used by the transfer screen.'],
                    ['name' => 'List SIM cards', 'method' => 'GET', 'path' => '/sim-cards', 'auth' => true, 'description' => 'Admin-only paginated SIM inventory.'],
                    ['name' => 'Unassigned SIM cards', 'method' => 'GET', 'path' => '/sim-cards/unassigned', 'auth' => true, 'description' => 'Admin-only list of SIMs ready for assignment.'],
                    ['name' => 'SIM stats', 'method' => 'GET', 'path' => '/sim-cards/stats/overview', 'auth' => true, 'description' => 'Admin-only SIM totals and balances.'],
                    ['name' => 'SIM balance', 'method' => 'GET', 'path' => '/sim-cards/1/balance', 'auth' => true, 'description' => 'Get balance and data balance for one SIM.'],
                    [
                        'name' => 'Create SIM card',
                        'method' => 'POST',
                        'path' => '/sim-cards',
                        'auth' => true,
                        'description' => 'Admin-only SIM creation.',
                        'body' => [
                            'sim_number' => 'SIM-API-001',
                            'phone_number' => '+250781111111',
                            'tariff_plan' => 'prepaid',
                            'balance' => 0,
                        ],
                    ],
                    [
                        'name' => 'Assign SIM card',
                        'method' => 'PUT',
                        'path' => '/sim-cards/1/assign',
                        'auth' => true,
                        'description' => 'Assign a SIM card to a customer.',
                        'body' => ['customer_id' => 1],
                    ],
                    [
                        'name' => 'Update SIM status',
                        'method' => 'PUT',
                        'path' => '/sim-cards/1/status',
                        'auth' => true,
                        'description' => 'Set SIM status to active, inactive, or suspended.',
                        'body' => ['status' => 'active'],
                    ],
                ],
            ],
            [
                'group' => 'Transactions',
                'items' => [
                    ['name' => 'List transactions', 'method' => 'GET', 'path' => '/transactions', 'auth' => true, 'description' => 'Paginated transaction list. Admins see all records.'],
                    ['name' => 'Transaction stats', 'method' => 'GET', 'path' => '/transactions/stats/overview', 'auth' => true, 'description' => 'Admin-only transaction totals.'],
                    ['name' => 'Show transaction', 'method' => 'GET', 'path' => '/transactions/1', 'auth' => true, 'description' => 'Get a single transaction by ID.'],
                    [
                        'name' => 'Create transfer',
                        'method' => 'POST',
                        'path' => '/transactions',
                        'auth' => true,
                        'description' => 'Create a transfer, data purchase, or recharge transaction.',
                        'body' => [
                            'type' => 'transfer',
                            'from_sim_id' => 1,
                            'to_sim_id' => 2,
                            'amount' => 1000,
                            'description' => 'API test transfer',
                        ],
                    ],
                    ['name' => 'Approve transaction', 'method' => 'POST', 'path' => '/transactions/1/approve', 'auth' => true, 'description' => 'Admin-only transaction approval.'],
                    ['name' => 'Cancel transaction', 'method' => 'POST', 'path' => '/transactions/1/cancel', 'auth' => true, 'description' => 'Admin-only transaction cancellation.'],
                ],
            ],
            [
                'group' => 'Wallet',
                'items' => [
                    ['name' => 'Show wallet', 'method' => 'GET', 'path' => '/wallet', 'auth' => true, 'description' => 'Return the authenticated user wallet.'],
                    ['name' => 'Wallet stats', 'method' => 'GET', 'path' => '/wallet/stats', 'auth' => true, 'description' => 'Return wallet balances and spend totals.'],
                    ['name' => 'Add balance', 'method' => 'POST', 'path' => '/wallet/add-balance', 'auth' => true, 'description' => 'Add money to the wallet.', 'body' => ['amount' => 1000]],
                    ['name' => 'Deduct balance', 'method' => 'POST', 'path' => '/wallet/deduct-balance', 'auth' => true, 'description' => 'Deduct money from the wallet.', 'body' => ['amount' => 500]],
                    ['name' => 'Add data', 'method' => 'POST', 'path' => '/wallet/add-data', 'auth' => true, 'description' => 'Add data balance to the wallet.', 'body' => ['data_amount' => 100]],
                ],
            ],
        ];

        $apiStatus = [
            'base_url' => url('/api'),
            'sanctum_installed' => class_exists(\Laravel\Sanctum\Sanctum::class),
            'token_table_ready' => Schema::hasTable('personal_access_tokens'),
            'protected_count' => collect($endpoints)->sum(fn ($group) => collect($group['items'])->where('auth', true)->count()),
            'public_count' => collect($endpoints)->sum(fn ($group) => collect($group['items'])->where('auth', false)->count()),
        ];

        return view('admin.api-checker', compact('endpoints', 'apiStatus'));
    }
}
