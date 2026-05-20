<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\SimCard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $transactions = Transaction::with('fromSim', 'toSim')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Users can only view their own transactions
            $customerSimCardIds = $user->customer->simCards->pluck('id')->toArray();
            $transactions = Transaction::with('fromSim', 'toSim')
                ->whereIn('from_sim_id', $customerSimCardIds)
                ->orWhereIn('to_sim_id', $customerSimCardIds)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        return response()->json($transactions, 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:transfer,data_purchase,recharge',
                'from_sim_id' => 'required_if:type,transfer|exists:sim_cards,id',
                'to_sim_id' => 'required_if:type,transfer|exists:sim_cards,id',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'sometimes|string|max:255',
            ]);

            if ($validated['type'] === 'transfer') {
                return $this->processTransfer($request, $validated);
            } elseif ($validated['type'] === 'data_purchase') {
                return $this->processDataPurchase($request, $validated);
            } elseif ($validated['type'] === 'recharge') {
                return $this->processRecharge($request, $validated);
            }
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    private function processTransfer($request, $validated)
    {
        $fromSim = SimCard::find($validated['from_sim_id']);
        $toSim = SimCard::find($validated['to_sim_id']);

        if (!$fromSim || !$toSim) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        // Check ownership
        if ($request->user()->isUser() && $fromSim->customer_id !== $request->user()->customer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fee = $validated['amount'] * 0.05; // 5% fee
        
        if (!$fromSim->hasSufficientBalance($validated['amount'] + $fee)) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $transaction = Transaction::create([
            'from_sim_id' => $validated['from_sim_id'],
            'to_sim_id' => $validated['to_sim_id'],
            'amount' => $validated['amount'],
            'fee' => $fee,
            'status' => 'pending',
            'description' => $validated['description'] ?? 'Money transfer',
        ]);

        return response()->json([
            'message' => 'Transfer initiated successfully',
            'transaction' => $transaction
        ], 201);
    }

    private function processDataPurchase($request, $validated)
    {
        $simCard = SimCard::find($validated['from_sim_id'] ?? null);

        if (!$simCard) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        if ($request->user()->isUser() && $simCard->customer_id !== $request->user()->customer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$simCard->hasSufficientBalance($validated['amount'])) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // Deduct money, add data (assuming 1 unit = 1 MB per currency unit)
        $simCard->deductBalance($validated['amount']);
        $simCard->addDataBalance($validated['amount']);

        $transaction = Transaction::create([
            'from_sim_id' => $validated['from_sim_id'],
            'to_sim_id' => $validated['from_sim_id'],
            'amount' => $validated['amount'],
            'fee' => 0,
            'status' => 'approved',
            'description' => 'Data purchase: ' . $validated['amount'] . ' MB',
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Data purchased successfully',
            'transaction' => $transaction
        ], 201);
    }

    private function processRecharge($request, $validated)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet || !$wallet->deductBalance($validated['amount'])) {
            return response()->json(['message' => 'Insufficient wallet balance'], 400);
        }

        $simCard = SimCard::find($validated['from_sim_id'] ?? null);
        if ($simCard) {
            $simCard->addBalance($validated['amount']);
        }

        $transaction = Transaction::create([
            'from_sim_id' => $validated['from_sim_id'],
            'to_sim_id' => $validated['from_sim_id'],
            'amount' => $validated['amount'],
            'fee' => 0,
            'status' => 'approved',
            'description' => 'Recharge',
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Recharge successful',
            'transaction' => $transaction
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $transaction = Transaction::with('fromSim', 'toSim')->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction, 200);
    }

    public function approve(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Only pending transactions can be approved'], 400);
        }

        $transaction->approve();

        return response()->json([
            'message' => 'Transaction approved successfully',
            'transaction' => $transaction
        ], 200);
    }

    public function cancel(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transaction = Transaction::find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Only pending transactions can be cancelled'], 400);
        }

        $transaction->cancel();

        return response()->json([
            'message' => 'Transaction cancelled successfully',
            'transaction' => $transaction
        ], 200);
    }

    public function getStats(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total_transactions' => Transaction::count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'approved_transactions' => Transaction::where('status', 'approved')->count(),
            'cancelled_transactions' => Transaction::where('status', 'cancelled')->count(),
            'total_amount' => Transaction::where('status', 'approved')->sum('amount'),
        ], 200);
    }
}
