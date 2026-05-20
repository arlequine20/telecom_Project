<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customers = Customer::with('simCards', 'user')->paginate(15);
        return response()->json($customers, 200);
    }

    public function store(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers',
                'phone' => 'required|unique:customers',
                'address' => 'required|string',
                'national_id' => 'required|unique:customers',
                'date_of_birth' => 'required|date',
                'user_id' => 'nullable|exists:users,id',
            ]);

            $customer = Customer::create($validated);

            return response()->json([
                'message' => 'Customer created successfully',
                'customer' => $customer
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        $customer = Customer::with('simCards', 'transactions')->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        // User can only view their own customer profile
        if ($request->user()->isUser() && $request->user()->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($customer, 200);
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        try {
            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:customers,email,' . $id,
                'phone' => 'sometimes|unique:customers,phone,' . $id,
                'address' => 'sometimes|string',
                'national_id' => 'sometimes|unique:customers,national_id,' . $id,
                'date_of_birth' => 'sometimes|date',
                'status' => 'sometimes|in:active,inactive',
            ]);

            $customer->update($validated);

            return response()->json([
                'message' => 'Customer updated successfully',
                'customer' => $customer
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully'], 200);
    }

    public function getStats(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'inactive_customers' => Customer::where('status', 'inactive')->count(),
        ], 200);
    }
}