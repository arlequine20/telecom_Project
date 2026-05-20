<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SimCard;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($sim->customer_id !== null) {
            return redirect()->route('admin.sim-cards')->with('error', 'This SIM card is already assigned and cannot be reassigned.');
        }

        $customers = Customer::orderBy('first_name')->get();
        return view('admin.assign-sim', compact('sim', 'customers'));
    }

    public function assignSimCard(Request $request, SimCard $sim)
    {
        if ($sim->customer_id !== null) {
            return redirect()->route('admin.sim-cards')->with('error', 'This SIM card is already assigned and cannot be reassigned.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::find($validated['customer_id']);
        $sim->customer_id = $customer->id;
        $sim->status = 'active';
        $sim->assigned_at = now();
        $sim->last_activity_at = now();
        $sim->save();

        return redirect()->route('admin.sim-cards')->with('success', 'SIM card assigned to ' . $customer->full_name . '.');
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
}