<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SimCard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SimCardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $simCards = SimCard::with('customer', 'sentTransactions', 'receivedTransactions')->paginate(15);
        return response()->json($simCards, 200);
    }

    public function unassigned(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $unassignedSimCards = SimCard::whereNull('customer_id')->get();
        return response()->json($unassignedSimCards, 200);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'sim_number' => 'required|string|unique:sim_cards',
                'phone_number' => 'required|string|unique:sim_cards',
                'balance' => 'sometimes|numeric|min:0',
                'tariff_plan' => 'required|in:prepaid,postpaid',
                'status' => 'sometimes|in:active,inactive,suspended',
            ]);

            $simCard = SimCard::create([
                'sim_number' => $validated['sim_number'],
                'phone_number' => $validated['phone_number'],
                'balance' => $validated['balance'] ?? 0,
                'tariff_plan' => $validated['tariff_plan'],
                'status' => 'inactive', // Default to inactive until assigned
            ]);

            return response()->json([
                'message' => 'SIM card created successfully',
                'sim_card' => $simCard
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        $simCard = SimCard::with('customer', 'sentTransactions', 'receivedTransactions')->find($id);

        if (!$simCard) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        return response()->json($simCard, 200);
    }

    public function assign(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $simCard = SimCard::find($id);

        if (!$simCard) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        try {
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
            ]);

            $simCard->customer_id = $validated['customer_id'];
            $simCard->assigned_at = now();
            $simCard->status = 'active'; // Automatically activate when assigned
            $simCard->save();

            return response()->json([
                'message' => 'SIM card assigned successfully',
                'sim_card' => $simCard
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $simCard = SimCard::find($id);

        if (!$simCard) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        try {
            $validated = $request->validate([
                'status' => 'required|in:active,inactive,suspended',
            ]);

            $simCard->status = $validated['status'];
            $simCard->save();

            return response()->json([
                'message' => 'SIM card status updated successfully',
                'sim_card' => $simCard
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function getBalance(Request $request, $id)
    {
        $simCard = SimCard::find($id);

        if (!$simCard) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        return response()->json([
            'sim_number' => $simCard->sim_number,
            'balance' => $simCard->balance,
            'data_balance' => $simCard->data_balance,
            'status' => $simCard->status,
        ], 200);
    }

    public function lookupByPhone(Request $request, $phone)
    {
        $simCard = SimCard::findByPhone($phone);

        if (!$simCard) {
            return response()->json(['message' => 'SIM card not found'], 404);
        }

        $simCard->load(['customer', 'customer.user']);

        $customerName = 'Unknown';
        if ($simCard->customer && $simCard->customer->user) {
            $customerName = $simCard->customer->user->name;
        }

        return response()->json([
            'phone_number' => $simCard->phone_number,
            'customer_name' => $customerName,
            'status' => $simCard->status,
            'found' => true
        ], 200);
    }

    public function getStats(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total_sim_cards' => SimCard::count(),
            'active_sim_cards' => SimCard::where('status', 'active')->count(),
            'inactive_sim_cards' => SimCard::where('status', 'inactive')->count(),
            'suspended_sim_cards' => SimCard::where('status', 'suspended')->count(),
            'assigned_sim_cards' => SimCard::whereNotNull('customer_id')->count(),
            'unassigned_sim_cards' => SimCard::whereNull('customer_id')->count(),
            'total_balance' => SimCard::sum('balance'),
        ], 200);
    }
}