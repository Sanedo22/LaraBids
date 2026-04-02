<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    
     //Display a listing of the resource
    public function index(Request $request)
    {
        $query = User::withTrashed()->with('roles');

        // Apply filters if needed, similar to DataTables search logic could be added here
        if ($request->has('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('roles', function($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%");
                      });
                      
                    if (is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            }
        }
        
        if ($request->has('role')) {
            $role = $request->role;
            if ($role === 'user_only') {
                $query->where(function($q) {
                    $q->doesntHave('roles')
                      ->orWhereHas('roles', function($roleQuery) {
                          $roleQuery->where('name', 'user');
                      });
                });
            } elseif ($role === 'super-admin') {
                $query->whereHas('roles', function($q) {
                    $q->whereIn('name', ['super-admin', 'super admin', 'Super Admin']);
                });
            } elseif ($role != 'all') {
                $query->role($role);
            }
        }
       
        if ($request->has('status') && $request->status !== 'all') {
             if ($request->status == 'deleted') {
                 $query->onlyTrashed();
             } elseif ($request->status == 'active') {
                 $query->withoutTrashed();
             }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(10);

        return UserResource::collection($users)->additional([
            'status' => true,
            'message' => 'Users retrieved successfully'
        ]);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email'
        ]);

        $email = strtolower(trim($request->email));
        $otp = rand(100000, 999999);
        
        \Illuminate\Support\Facades\Cache::put('admin_create_user_otp_' . $email, $otp, now()->addMinutes(10));

        \App\Jobs\SendOtpEmailJob::dispatch($email, $otp);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully to ' . $email
        ]);
    }
    
     // Store a newly created resource in storage.
     
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Admin can create user and admin roles
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('super-admin')) {
            if (!in_array($request->role, ['user', 'admin'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only create users with User or Admin role'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|min:2|max:255',
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:users,username'],
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', 'min:8', Rules\Password::defaults()],
            'role'     => 'required|exists:roles,name',
            'otp'      => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $validated = $validator->validated();

            $emailVerifiedAt = null;

            if (!empty($validated['otp'])) {
                $cacheKey = 'admin_create_user_otp_' . strtolower(trim($validated['email']));
                $cachedOtp = \Illuminate\Support\Facades\Cache::get($cacheKey);
                
                if ($cachedOtp && $cachedOtp == $validated['otp']) {
                    $emailVerifiedAt = now();
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid OTP provided for the email. User not created.'
                    ], 422);
                }
            }

            $user = User::create([
                'name'     => trim($validated['name']),
                'username' => strtolower(trim($validated['username'])),
                'email'    => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'email_verified_at' => $emailVerifiedAt,
                'created_by' => auth()->id(),
            ]);

            $user->assignRole($validated['role']);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'User created successfully',
                'data'    => new UserResource($user)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }


     // Display the specified resource.
    
    public function show($id)
    {
        $user = User::withTrashed()->with(['roles', 'strikes.auction', 'strikes.reporter'])->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => new UserResource($user)
        ]);
    }

    
     // Update the specified resource in storage
    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $currentUser = Auth::user();

        // Admin cannot update super admin or other admins permissions check
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('super-admin')) {
            if ($user->hasRole('super-admin') || ($user->hasRole('admin') && $user->id !== $currentUser->id)) {
                 return response()->json([
                    'status' => false,
                    'message' => 'You can only update User role accounts'
                ], 403);
            }
        }

        // Super admin cannot update other super admins
        if ($currentUser->hasRole('super-admin') && $user->hasRole('super-admin') && $user->id !== $currentUser->id) {
             return response()->json([
                'status' => false,
                'message' => 'You cannot update other Super Admin users'
            ], 403);
        }

        // Admin can assign user or admin role
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('super-admin')) {
            if ($request->has('role') && !in_array($request->role, ['user', 'admin'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only assign User or Admin role'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|min:2|max:255',
            'username' => 'required|string|alpha_dash|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'  => 'sometimes|exists:roles,name',
            'password' => ['nullable', 'confirmed', 'min:8', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $validated = $validator->validated();

            $user->update([
                'name'  => trim($validated['name']),
                'username' => strtolower(trim($validated['username'])),
                'email' => strtolower(trim($validated['email'])),
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Self role protection
            if ($request->has('role') && $user->id !== Auth::id()) {
                $user->syncRoles([$validated['role']]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'User updated successfully',
                'data'    => new UserResource($user->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    
     // Remove the specified resource from storage (Soft Delete).
     
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
             return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $currentUser = Auth::user();

        if ($user->id === Auth::id()) {
             return response()->json([
                'status' => false,
                'message' => 'You cannot delete yourself'
            ], 422);
        }

        // Check permissions
        if ($currentUser->hasRole('admin') && !$currentUser->hasRole('super-admin') && ($user->hasRole('super-admin') || $user->hasRole('admin'))) {
            return response()->json([
                'status' => false,
                'message' => 'You can only delete User role accounts'
            ], 403);
        }

        $user->update(['deleted_by' => Auth::id()]); // Create migration if this column doesn't exist, assuming logic from existing controller implies it might exist or be handled differently. Looking at existing controller: `$user->update(['deleted_by' => Auth::id()]);` is present.
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User moved to trash'
        ]);
    }

    
     // Restore the specified resource from storage.
     
    public function restore($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
             return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->restore();
        // Assuming 'deleted_by' exists as per existing controller logic
        $user->update(['deleted_by' => null]); 

        return response()->json([
            'status' => true,
            'message' => 'User restored successfully',
             'data'   => new UserResource($user)
        ]);
    }

    
     //Permanently remove the specified resource from storage.
     
    public function forceDelete($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
             return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        $currentUser = Auth::user();
        if ($user->id === Auth::id()) {
             return response()->json([
                'status' => false,
                'message' => 'You cannot delete yourself'
            ], 422);
        }

        // Permission checks
         if ($currentUser->hasRole('admin') && !$currentUser->hasRole('super-admin') && ($user->hasRole('super-admin') || $user->hasRole('admin'))) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $user->roles()->detach();
        $user->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'User permanently deleted'
        ]);
    }

    public function removeStrike($userId, $strikeId)
    {
        $user = User::withTrashed()->find($userId);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $strike = \App\Models\UserStrike::where('user_id', $user->id)
            ->find($strikeId);

        if (!$strike) {
            return response()->json([
                'status' => false,
                'message' => 'Strike not found'
            ], 404);
        }
            
        $strike->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'User strike removed successfully'
        ]);
    }

    // Bulk Actions
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'action' => 'required|string|in:delete,restore,force_delete'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ids = $request->ids;
        $action = $request->action;
        $currentUser = Auth::user();

        // Prevent self action
        if (in_array($currentUser->id, $ids)) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot perform bulk actions on your own account.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $users = User::withTrashed()->whereIn('id', $ids)->get();
            $processedCount = 0;

            foreach ($users as $user) {
                // Permission checks
                if ($currentUser->hasRole('super admin') && $user->hasRole('super admin') && $user->id !== $currentUser->id) {
                    if ($user->created_by !== $currentUser->id) {
                        continue; // Skip or throw error? In bulk, skipping might be better or collect errors
                    }
                }
                
                if ($currentUser->hasRole('admin') && !$currentUser->hasRole('super admin') && ($user->hasRole('super admin') || $user->hasRole('admin'))) {
                    continue;
                }

                if ($action === 'delete') {
                    $user->update(['deleted_by' => Auth::id()]);
                    $user->delete();
                    $processedCount++;
                } elseif ($action === 'restore') {
                    $user->restore();
                    $user->update(['deleted_by' => null]);
                    $processedCount++;
                } elseif ($action === 'force_delete') {
                    $user->roles()->detach();
                    $user->forceDelete();
                    $processedCount++;
                }
            }

            DB::commit();

            $actionMessage = match($action) {
                'delete' => 'suspended',
                'restore' => 'restored',
                'force_delete' => 'permanently deleted',
                default => 'processed'
            };

            return response()->json([
                'status' => true,
                'message' => $processedCount . " user(s) have been successfully {$actionMessage}."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }
}

