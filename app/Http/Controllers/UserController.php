<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
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

    // Generate numeric profile_id
    $latestUser = User::orderBy('profile_id', 'desc')->first();
    
    // Ensure we start with at least 1000
    $profileId = $latestUser && $latestUser->profile_id >= 1000 ? ($latestUser->profile_id + 1) : 1000;

    if ($profileId > 9999) {
        $profileId = 1000;
    }
    
    // Make sure the profile_id is unique
    while (User::where('profile_id', $profileId)->exists()) {
        $profileId++;
        if ($profileId > 9999) {
            throw new \Exception('No available profile IDs in the range 1000–9999');
        }
    }

    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
        'role_id' => $validatedData['role_id'] ?? null,
        'manager_id' => $validatedData['manager_id'] ?? null,
        'remember_token' => Str::random(60),
        'profile_id' => $profileId, // store as integer
    ]);

    $user->load(['role', 'manager']);
    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful',
        'user' => $user,
        'formatted_profile_id' => $user->formatted_profile_id,
        'token' => $token,
        'token_type' => 'Bearer',
    ], 201);
}


    /**
     * Login and send welcome email
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        try {
            if (config('mail.mailer') && config('mail.host') && config('mail.port') &&
                config('mail.username') && config('mail.password')) {

                Mail::send('emails.welcome', [
                    'user_name' => $user->name,
                    'password' => $request->password,
                    'company_name' => 'Divisarana',
                    'support_email' => 'support@divisarana.com',
                    'login_url' => url('http://app.optiomax.com'),
                    'user' => $user
                ], function ($message) use ($user) {
                    $message->to($user->email)->subject('Welcome to Divisarana - Your Account Details');
                });

                \Log::info('User credentials sent to: ' . $user->email);
            } else {
                \Log::warning("Mail configuration incomplete. Email not sent to: {$user->email}");
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send user credentials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $user->email
            ]);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load(['role', 'manager']),
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['role', 'manager', 'subordinates']);

        return response()->json($user);
    }

    /**
     * User menu permissions
     */
    public function permissions(Request $request)
    {
        $user = $request->user();

        if (!$user->role) {
            return response()->json(['menus' => []]);
        }

        $menus = $user->role->menus()
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'name', 'path', 'icon', 'sort_order']);

        return response()->json(['menus' => $menus]);
    }

    /**
     * Update profile
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

        if (isset($validated['password'])) {
            // Verify the current password using Auth attempt
            if (!Auth::guard('web')->attempt([
                'email' => $user->email,
                'password' => $validated['current_password']
            ])) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
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

    /**
     * Index, show, store, update, delete, restore, trashed, forceDelete, viewLogs, clearLogs, testLogging
     * (Include from your existing controller if needed, same logic as before)
     */

    // ... (Omit for brevity but you can keep all other methods exactly as they are in your original `UserController`)
}
