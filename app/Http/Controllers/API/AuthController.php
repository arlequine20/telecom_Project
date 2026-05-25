<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email:rfc|unique:users',
                'password' => 'required|min:6|confirmed',
                'role' => 'required|in:user,admin',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'user',
                'status' => 'active',
            ]);

            // Create wallet for users
            if ($user->role === 'user') {
                Wallet::create(['user_id' => $user->id]);
            }

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email:rfc|exists:users,email',
                'password' => 'required|min:6',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if ($user->status !== 'active') {
                return response()->json([
                    'message' => 'Account is not active'
                ], 403);
            }

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('wallet', 'customer', 'customer.simCards')
        ], 200);
    }
}
