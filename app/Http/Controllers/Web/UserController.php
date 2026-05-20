<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SimCard;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private function getCustomerId()
    {
        $user = Auth::user();
        if ($user && $user->customer) {
            return $user->customer->id;
        }

        abort(404, 'Customer profile not found.');
    }

    public function dashboard()
    {
        $customerId = $this->getCustomerId();
        
        $simCards = SimCard::where('customer_id', $customerId)->get();
        $mainBalance = $simCards->sum('balance');
        $recentTransactions = Transaction::whereIn('from_sim_id', $simCards->pluck('id'))
            ->orWhereIn('to_sim_id', $simCards->pluck('id'))
            ->with(['fromSim', 'toSim'])
            ->latest()
            ->take(5)
            ->get();
        
        return view('user.dashboard', compact('simCards', 'mainBalance', 'recentTransactions'));
    }
    
    public function transfer()
    {
        $customerId = $this->getCustomerId();
        $simCards = SimCard::where('customer_id', $customerId)
            ->where('status', 'active')
            ->get();
        
        return view('user.transfer', compact('simCards'));
    }

    public function showRechargeForm(SimCard $sim)
    {
        $customerId = $this->getCustomerId();
        if ($sim->customer_id !== $customerId) {
            abort(404);
        }

        $wallet = Auth::user()->wallet;
        return view('user.recharge', compact('sim', 'wallet'));
    }

    public function showWalletTopUpForm()
    {
        $wallet = Auth::user()->wallet;
        return view('user.wallet-topup', compact('wallet'));
    }

    public function walletTopUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $wallet = Auth::user()->wallet;
        if (!$wallet) {
            return back()->with('error', 'Wallet not found. Please contact support.');
        }

        try {
            DB::transaction(function () use ($wallet, $request) {
                $wallet = $wallet->fresh();
                $wallet->addBalance($request->input('amount'));
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Unable to top up wallet at this time.');
        }

        return redirect()->route('user.sims')->with('success', 'Wallet top-up successful.');
    }

    public function rechargeSimCard(Request $request, SimCard $sim)
    {
        $customerId = $this->getCustomerId();
        if ($sim->customer_id !== $customerId) {
            abort(404);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = $request->input('amount');
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return back()->with('error', 'Unable to locate authenticated user.');
        }

        $wallet = $user->wallet;
        if (!$wallet) {
            return back()->with('error', 'Wallet not found. Please contact support.');
        }

        try {
            DB::transaction(function () use ($amount, $sim, $user) {
                $lockedWallet = $user->wallet()->lockForUpdate()->first();

                if (!$lockedWallet || $lockedWallet->balance < $amount) {
                    throw new \RuntimeException('Insufficient wallet balance to recharge this SIM.');
                }

                if (!$lockedWallet->deductBalance($amount)) {
                    throw new \RuntimeException('Unable to deduct wallet balance.');
                }

                $lockedSim = SimCard::where('id', $sim->id)->lockForUpdate()->first();
                if (!$lockedSim) {
                    throw new \RuntimeException('SIM card not found.');
                }

                $lockedSim->balance += $amount;
                $lockedSim->last_activity_at = now();
                $lockedSim->save();
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('user.sims')->with('success', 'SIM recharge successful.');
    }
    
    public function sendTransfer(Request $request)
    {
        $request->validate([
            'from_sim' => 'required|exists:sim_cards,id',
            'to_phone' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!SimCard::findByPhone($value)) {
                        $fail('Recipient phone number not found in the system');
                    }
                },
            ],
            'amount' => 'required|numeric|min:1',
        ]);
        
        $fromSim = SimCard::find($request->from_sim);
        $toSim = SimCard::findByPhone($request->to_phone);
        
        if (!$fromSim || !$toSim) {
            return back()->with('error', 'SIM card not found');
        }

        $customerId = $this->getCustomerId();
        if ($fromSim->customer_id !== $customerId) {
            return back()->with('error', 'Selected SIM card does not belong to your account.');
        }
        
        if ($fromSim->status !== 'active' || $toSim->status !== 'active') {
            return back()->with('error', 'One of the SIM cards is not active');
        }
        
        $fee = max($request->amount * 0.02, 0.10);
        $totalDeduction = $request->amount + $fee;
        
        if ($fromSim->balance < $totalDeduction) {
            return back()->with('error', 'Insufficient balance. You need RWF ' . number_format($totalDeduction, 2));
        }

        if (!Transaction::requiresAdminReview($request->amount)) {
            try {
                DB::transaction(function () use ($fromSim, $toSim, $request, $fee, $totalDeduction) {
                    $lockedFromSim = SimCard::where('id', $fromSim->id)->lockForUpdate()->first();
                    $lockedToSim = SimCard::where('id', $toSim->id)->lockForUpdate()->first();

                    if (!$lockedFromSim || !$lockedToSim) {
                        throw new \RuntimeException('SIM card not found.');
                    }

                    if ($lockedFromSim->balance < $totalDeduction) {
                        throw new \RuntimeException('Insufficient balance to complete the transfer.');
                    }

                    $lockedFromSim->balance -= $totalDeduction;
                    $lockedFromSim->last_activity_at = now();
                    $lockedFromSim->save();

                    $lockedToSim->balance += $request->amount;
                    $lockedToSim->last_activity_at = now();
                    $lockedToSim->save();

                    Transaction::create([
                        'from_sim_id' => $lockedFromSim->id,
                        'to_sim_id' => $lockedToSim->id,
                        'amount' => $request->amount,
                        'fee' => $fee,
                        'description' => $request->description,
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                });
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }

            return redirect()->route('user.dashboard')->with('success', 'Money sent successfully. RWF ' . number_format($request->amount, 2) . ' has been deducted from your SIM.');
        }

        try {
            Transaction::create([
                'from_sim_id' => $fromSim->id,
                'to_sim_id' => $toSim->id,
                'amount' => $request->amount,
                'fee' => $fee,
                'description' => $request->description,
                'status' => 'pending',
            ]);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('user.dashboard')->with('success', 'Transfer request submitted. It will be reviewed by an administrator because the amount is above the review threshold.');
    }

    public function requestReversal(Request $request, Transaction $transaction)
    {
        $customerId = $this->getCustomerId();

        if ($transaction->fromSim->customer_id !== $customerId) {
            abort(403);
        }

        if ($transaction->status !== Transaction::STATUS_APPROVED) {
            return back()->with('error', 'Only approved transfers can be requested for reversal.');
        }

        $transaction->status = Transaction::STATUS_REVERSAL_REQUESTED;
        $transaction->save();

        return back()->with('success', 'Reversal request submitted. An administrator will review it.');
    }
    
    public function history()
    {
        $customerId = $this->getCustomerId();
        $simCards = SimCard::where('customer_id', $customerId)->get();
        $simIds = $simCards->pluck('id');
        
        $transactions = Transaction::whereIn('from_sim_id', $simIds)
            ->orWhereIn('to_sim_id', $simIds)
            ->with(['fromSim', 'toSim'])
            ->latest()
            ->paginate(20);
        
        return view('user.history', compact('transactions', 'simCards'));
    }
    
    public function mySimCards()
    {
        $customerId = $this->getCustomerId();
        $simCards = SimCard::where('customer_id', $customerId)->get();
        return view('user.sims', compact('simCards'));
    }
    
    public function profile()
    {
        $customerId = $this->getCustomerId();
        $customer = Customer::find($customerId);
        $simCards = SimCard::where('customer_id', $customerId)->get();
        
        return view('user.profile', compact('customer', 'simCards'));
    }
}