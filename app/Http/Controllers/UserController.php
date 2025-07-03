<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'manager']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role_id') && $request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by manager
        if ($request->has('manager_id') && $request->manager_id) {
            $query->where('manager_id', $request->manager_id);
        }

        // Include trashed records if requested
        if ($request->has('with_trashed') && $request->with_trashed) {
            $query->withTrashed();
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->load(['role', 'manager']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['role', 'manager', 'subordinates', 'createdTasks', 'assignedTasks']);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|nullable|exists:roles,id',
            'manager_id' => 'sometimes|nullable|exists:users,id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Prevent self-assignment as manager
        if (isset($validated['manager_id']) && $validated['manager_id'] == $user->id) {
            return response()->json([
                'message' => 'User cannot be their own manager'
            ], 422);
        }

        $user->update($validated);
        $user->load(['role', 'manager']);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Restore a soft deleted user.
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $user
        ]);
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json([
            'message' => 'User permanently deleted'
        ]);
    }

    /**
     * Handle user login and send welcome email.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Send welcome email with password
            try {
                // Check if mail configuration is complete
                if (config('mail.mailer') && 
                    config('mail.host') && 
                    config('mail.port') && 
                    config('mail.username') && 
                    config('mail.password')) {
                    
                    $user_name = $user->name;
                    $company_name = 'Divisarana';
                    $support_email = 'support@divisarana.com';
                    $login_url = url('http://app.optiomax.com');
                    
                    Mail::send('emails.welcome', [
                        'user_name' => $user_name,
                        'password' => $request->password,
                        'company_name' => $company_name,
                        'support_email' => $support_email,
                        'login_url' => $login_url,
                        'user' => $user
                    ], function($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Welcome to Divisarana - Your Account Details');
                    });
                    
                    // Log activity
                    \Log::info('User credentials sent to: ' . $user->email);
                } else {
                    \Log::warning("Mail configuration incomplete. Email not sent to: {$user->email}", [
                        'mail_config' => [
                            'mailer' => config('mail.mailer') ? 'set' : 'missing',
                            'host' => config('mail.host') ? 'set' : 'missing',
                            'port' => config('mail.port') ? 'set' : 'missing',
                            'username' => config('mail.username') ? 'set' : 'missing',
                            'password' => config('mail.password') ? 'set' : 'missing'
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send user credentials: ', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'email' => $user->email
                ]);
            }

            return response()->json([
                'message' => 'Login successful',
                'user' => $user->load(['role', 'manager']),
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }
    
    /**
     * Get trashed users.
     */
    public function trashed(Request $request)
    {
        $query = User::onlyTrashed()->with(['role', 'manager']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }
    
    /**
     * View recent log entries for debugging purposes
     * Requires admin privileges
     */
    public function viewLogs(Request $request)
    {
        // Check if user has admin role
        if (Auth::user() && Auth::user()->role && Auth::user()->role->name === 'Admin') {
            try {
                $logPath = storage_path('logs/laravel.log');
                
                if (!file_exists($logPath)) {
                    return response()->json([
                        'message' => 'Log file does not exist',
                        'path' => $logPath
                    ], 404);
                }
                
                // Get the last X lines from the log file
                $lines = $request->get('lines', 50);
                $logs = $this->tailFile($logPath, $lines);
                
                return response()->json([
                    'logs' => $logs,
                    'log_path' => $logPath
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error retrieving logs',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    /**
     * Clear the Laravel log file
     * Requires admin privileges
     */
    public function clearLogs()
    {
        if (Auth::user() && Auth::user()->role && Auth::user()->role->name === 'Admin') {
            try {
                $logPath = storage_path('logs/laravel.log');
                
                if (file_exists($logPath)) {
                    file_put_contents($logPath, '');
                    return response()->json(['message' => 'Logs cleared successfully']);
                }
                
                return response()->json(['message' => 'Log file does not exist'], 404);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error clearing logs',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    /**
     * Test logging to ensure logs are being written correctly
     */
    public function testLogging()
    {
        if (Auth::user() && Auth::user()->role && Auth::user()->role->name === 'Admin') {
            try {
                \Log::emergency('This is an emergency test log');
                \Log::alert('This is an alert test log');
                \Log::critical('This is a critical test log');
                \Log::error('This is an error test log');
                \Log::warning('This is a warning test log');
                \Log::notice('This is a notice test log');
                \Log::info('This is an info test log');
                \Log::debug('This is a debug test log');
                
                return response()->json([
                    'message' => 'Test logs created successfully',
                    'log_path' => storage_path('logs/laravel.log'),
                    'log_config' => [
                        'channel' => config('logging.default'),
                        'level' => config('logging.channels.' . config('logging.default') . '.level'),
                        'driver' => config('logging.channels.' . config('logging.default') . '.driver'),
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Error testing logs',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
        
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    /**
     * Helper method to get the last X lines from a file
     */
    private function tailFile($filepath, $lines = 50)
    {
        $file = file($filepath);
        if (count($file) < $lines) {
            return $file;
        }
        
        return array_slice($file, -$lines);
    }
}
