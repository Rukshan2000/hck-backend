<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Load user relationships
        $user->load(['role', 'manager']);

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|integer',
            'manager_id' => 'nullable|integer',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $validatedData['role_id'] ?? null,
            'manager_id' => $validatedData['manager_id'] ?? null,
            'remember_token' => Str::random(60),
        ]);

        $user->load(['role', 'manager']);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Logout user (Revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['role', 'manager', 'subordinates']);

        return response()->json($user);
    }

    /**
     * Get user's menu permissions
     */
    public function permissions(Request $request)
    {
        $user = $request->user();

        if (!$user->role) {
            return response()->json([
                'menus' => []
            ]);
        }

        $menus = $user->role->menus()
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'path', 'icon', 'sort_order']);

        return response()->json([
            'menus' => $menus
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if (isset($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            $validated['password'] = Hash::make($validated['password']);
        }

        unset($validated['current_password']);

        $user->update($validated);
        $user->load(['role', 'manager']);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
