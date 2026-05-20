<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        return response()->json($wallet, 200);
    }

    public function addBalance(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            $wallet->addBalance($validated['amount']);

            return response()->json([
                'message' => 'Balance added successfully',
                'wallet' => $wallet
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function deductBalance(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            if (!$wallet->deductBalance($validated['amount'])) {
                return response()->json(['message' => 'Insufficient balance'], 400);
            }

            return response()->json([
                'message' => 'Balance deducted successfully',
                'wallet' => $wallet
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function addDataBalance(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        try {
            $validated = $request->validate([
                'data_amount' => 'required|numeric|min:0.01',
            ]);

            $wallet->addDataBalance($validated['data_amount']);

            return response()->json([
                'message' => 'Data balance added successfully',
                'wallet' => $wallet
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function getStats(Request $request)
    {
        return response()->json([
            'balance' => $request->user()->wallet->balance,
            'total_spend' => $request->user()->wallet->total_spend,
            'data_balance' => $request->user()->wallet->data_balance,
        ], 200);
    }
}
