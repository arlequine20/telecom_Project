<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SimCard;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{
    // Initiate transfer (creates pending transaction)
    public function initiateTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_phone' => 'required|string',
            'to_phone' => 'required|string|different:from_phone',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $fromSim = SimCard::findByPhone($request->from_phone);
        $toSim = SimCard::findByPhone($request->to_phone);

        if (!$fromSim || !$toSim) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient phone number not found in the system'
            ], 422);
        }
        
        // Check if sender SIM is active
        if (!$fromSim->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Sender SIM card is not active'
            ], 400);
        }
        
        // Check if receiver SIM exists and is active
        if (!$toSim->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Receiver SIM card is not active'
            ], 400);
        }
        
        // Calculate fee (2% of amount, minimum $0.10)
        $fee = max($request->amount * 0.02, 0.10);
        $totalDeduction = $request->amount + $fee;
        
        // Check sufficient balance
        if (!$fromSim->hasSufficientBalance($totalDeduction)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance',
                'data' => [
                    'balance' => $fromSim->balance,
                    'required' => $totalDeduction,
                    'amount' => $request->amount,
                    'fee' => $fee
                ]
            ], 400);
        }
        
        $transactionData = [
            'from_sim_id' => $fromSim->id,
            'to_sim_id' => $toSim->id,
            'amount' => $request->amount,
            'fee' => $fee,
            'description' => $request->description,
        ];

        if (Transaction::requiresAdminReview($request->amount)) {
            $transactionData['status'] = 'pending';
        } else {
            $transactionData['status'] = 'approved';
            $transactionData['approved_at'] = now();
            $fromSim->balance -= $request->amount + $fee;
            $fromSim->save();
            $toSim->balance += $request->amount;
            $toSim->save();
        }

        $transaction = Transaction::create($transactionData);
        
        return response()->json([
            'success' => true,
            'message' => Transaction::requiresAdminReview($request->amount)
                ? 'Transfer initiated. Waiting for approval.'
                : 'Transfer completed successfully.',
            'data' => [
                'transaction_reference' => $transaction->transaction_reference,
                'from_phone' => $fromSim->phone_number,
                'to_phone' => $toSim->phone_number,
                'amount' => $transaction->amount,
                'fee' => $transaction->fee,
                'total_deduction' => $totalDeduction,
                'status' => $transaction->status
            ]
        ]);
    }
    
    // Approve transfer
    public function approveTransfer($reference)
    {
        $transaction = Transaction::where('transaction_reference', $reference)
            ->where('status', 'pending')
            ->first();
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found or already processed'
            ], 404);
        }
        
        // Double-check balance before approval
        $fromSim = $transaction->fromSim;
        $totalDeduction = $transaction->amount + $transaction->fee;
        
        if (!$fromSim->hasSufficientBalance($totalDeduction)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance to complete transaction'
            ], 400);
        }
        
        // Approve the transaction
        $transaction->approve();
        
        return response()->json([
            'success' => true,
            'message' => 'Transfer approved successfully',
            'data' => [
                'transaction_reference' => $transaction->transaction_reference,
                'status' => $transaction->status,
                'approved_at' => $transaction->approved_at,
                'new_sender_balance' => $transaction->fromSim->fresh()->balance,
                'new_receiver_balance' => $transaction->toSim->fresh()->balance
            ]
        ]);
    }
    
    // Cancel transfer
    public function cancelTransfer($reference)
    {
        $transaction = Transaction::where('transaction_reference', $reference)
            ->where('status', 'pending')
            ->first();
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found or already processed'
            ], 404);
        }
        
        $transaction->cancel();
        
        return response()->json([
            'success' => true,
            'message' => 'Transfer cancelled successfully',
            'data' => [
                'transaction_reference' => $transaction->transaction_reference,
                'status' => $transaction->status,
                'cancelled_at' => $transaction->cancelled_at
            ]
        ]);
    }
    
    // Get transaction status
    public function transactionStatus($reference)
    {
        $transaction = Transaction::with(['fromSim', 'toSim'])
            ->where('transaction_reference', $reference)
            ->first();
        
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'transaction_reference' => $transaction->transaction_reference,
                'from_phone' => $transaction->fromSim->phone_number,
                'to_phone' => $transaction->toSim->phone_number,
                'amount' => $transaction->amount,
                'fee' => $transaction->fee,
                'status' => $transaction->status,
                'description' => $transaction->description,
                'created_at' => $transaction->created_at,
                'approved_at' => $transaction->approved_at,
                'cancelled_at' => $transaction->cancelled_at
            ]
        ]);
    }
}