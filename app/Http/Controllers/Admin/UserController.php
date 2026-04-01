<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::withTrashed()->with('roles');

            // Apply Filters Before DataTables wraps it
            if ($request->filled('role')) {
                $role = $request->role;
                if ($role === 'user_only') {
                    // Find standard users (either 'user' role or no role)
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
                } else {
                    $query->whereHas('roles', function($q) use ($role) {
                        $q->where('name', $role);
                    });
                }
            }

            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->whereNull('users.deleted_at');
                } elseif ($request->status === 'deleted') {
                    $query->whereNotNull('users.deleted_at');
                }
            }

            if ($request->filled('start_date')) {
                $query->whereDate('users.created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('users.created_at', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->order(function ($q) use ($request) {
                    if ($request->filled('sort')) {
                        if ($request->sort === 'oldest') {
                            $q->orderBy('users.created_at', 'asc');
                        } else {
                            $q->orderBy('users.created_at', 'desc');
                        }
                    } else {
                        $q->orderBy('users.created_at', 'desc');
                    }
                })
                ->addIndexColumn()
                ->addColumn('checkbox', function($row){
                    $isTrashed = $row->trashed() ? 1 : 0;
                    return '<div class="custom-control custom-checkbox text-center"><input type="checkbox" class="custom-control-input user-checkbox" id="user_'.$row->id.'" value="'.$row->id.'" data-is-trashed="'.$isTrashed.'"><label class="custom-control-label" for="user_'.$row->id.'"></label></div>';
                })
                ->addColumn('role_name', function($row){
                    if ($row->roles->isEmpty()) {
                        return 'User';
                    }
                    
                    $roleNames = [];
                    foreach($row->roles as $role){
                        $roleNames[] = ucwords(str_replace('-', ' ', $role->name));
                    }
                    return implode(', ', $roleNames);
                })
                ->addColumn('status', function($row){
                    if($row->trashed()){
                        return '<span class="badge badge-danger">Deleted</span>';
                    }
                    return '<span class="badge badge-success">Active</span>';
                })
                ->addColumn('joined_date', function($row){
                    return $row->created_at->format('M d, Y');
                })
                ->addColumn('action', function($row){
                    $currentUser = Auth::user();
                    $canManageUser = false;

                    // Permission logic
                    if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
                        $canManageUser = $row->hasRole('user');
                    }
                    elseif ($currentUser->isSuperAdmin()) {
                        $canManageUser = ($row->id === $currentUser->id) || 
                                        ($row->created_by === $currentUser->id) || 
                                        (!$row->isSuperAdmin());
                    }

                    $btn = '<div class="d-flex justify-content-center gap-1">';

                    // View
                    $btn .= '<a href="'.route('admin.users.show', $row->id).'" class="btn btn-outline-info btn-sm btn-action" title="View"><i class="fas fa-eye"></i></a>';

                    if ($canManageUser) {
                        if (!$row->trashed()) {
                            // Edit
                            $btn .= '<a href="'.route('admin.users.edit', $row->id).'" class="btn btn-outline-primary btn-sm btn-action" title="Edit"><i class="fas fa-edit"></i></a>';

                            // Delete
                            if(auth()->id() !== $row->id) {
                                $btn .= '<button type="button" class="btn btn-outline-danger btn-sm delete-user btn-action" data-id="'.$row->id.'" data-url="'.route('admin.users.destroy', $row->id).'" title="Delete"><i class="fas fa-trash"></i></button>';
                            } else {
                                $btn .= '<button class="btn btn-outline-secondary btn-sm btn-action" disabled><i class="fas fa-user-lock"></i></button>';
                            }
                        } else {
                            // Restore
                            $btn .= '<button type="button" class="btn btn-outline-success btn-sm restore-user btn-action" data-id="'.$row->id.'" data-url="'.route('admin.users.restore', $row->id).'" title="Restore"><i class="fas fa-trash-restore"></i></button>';

                            // Force Delete
                            if(auth()->id() !== $row->id) {
                                $btn .= '<button type="button" class="btn btn-outline-danger btn-sm force-delete-user btn-action" data-id="'.$row->id.'" data-url="'.route('admin.users.force_delete', $row->id).'" title="Permanent Delete"><i class="fas fa-times"></i></button>';
                            }
                        }
                    } else {
                        $btn .= '<button class="btn btn-outline-secondary btn-sm btn-action" disabled title="Protected User"><i class="fas fa-shield-alt"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['checkbox', 'role_name', 'status', 'action'])
                ->make(true);
        }

        return view('admin.users.index');
    }

    public function create()
    {
        $currentUser = Auth::user();

        // Admin can create user and admin roles
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $roles = Role::where('guard_name', 'web')
                ->whereIn('name', ['user', 'admin'])
                ->get();
        } else {
            // Super admin can create all roles
            $roles = Role::where('guard_name', 'web')->get();
        }

        return view('admin.users.create', compact('roles'));
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'max:255', 'not_regex:/\+/']
        ], [
            'email.not_regex' => 'Please enter a valid standard email address.'
        ]);

        $email = strtolower(trim($request->email));
        $otp = rand(100000, 999999);
        
        session([
            'admin_create_user_otp_' . $email => $otp,
            'admin_create_user_otp_time_' . $email => now(),
        ]);

        \App\Jobs\SendOtpEmailJob::dispatch($email, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to ' . $email
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Admin can create user and admin roles
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            if (!in_array($request->role, ['user', 'admin'])) {
                return back()
                    ->withInput()
                    ->with('error', 'You can only create users with User or Admin role');
            }
        }

        // No restriction for super admin - can create all roles

        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email', 'not_regex:/\+/'],
            'password' => ['required', 'confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'],
            'role'     => 'required|exists:roles,name',
            'otp'      => 'required|string|size:6',
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (symbol).',
            'email.not_regex' => 'Please enter a valid standard email address.',
        ]);

        $emailVerifiedAt = null;

        if (!empty($validated['otp'])) {
            $emailKey = strtolower(trim($validated['email']));
            $sessionOtp = session('admin_create_user_otp_' . $emailKey);
            $sessionTime = session('admin_create_user_otp_time_' . $emailKey);
            
            if ($sessionOtp && $sessionOtp == $validated['otp']) {
                if (!$sessionTime || now()->diffInMinutes($sessionTime) > 10) {
                    session()->forget(['admin_create_user_otp_' . $emailKey, 'admin_create_user_otp_time_' . $emailKey]);
                    return back()->withInput()->with('error', 'OTP has expired. Please request a new one.');
                }
                $emailVerifiedAt = now();
                session()->forget(['admin_create_user_otp_' . $emailKey, 'admin_create_user_otp_time_' . $emailKey]);
            } else {
                return back()->withInput()->with('error', 'Invalid OTP provided for the email. User not created.');
            }
        }

        DB::transaction(function () use ($validated, $emailVerifiedAt) {
            $user = User::create([
                'name'     => trim($validated['name']),
                'username' => strtolower(trim($validated['username'])),
                'email'    => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
                'email_verified_at' => $emailVerifiedAt,
                'created_by' => auth()->id(),
            ]);

            $user->assignRole($validated['role']);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully');
    }

    public function show($id)
    {
        $user = User::withTrashed()
            ->with(['roles', 'strikes.auction', 'strikes.reporter'])
            ->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $currentUser = Auth::user();

        // Admin cannot edit super admin or other admins
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'You can only edit User role accounts');
            }
        }

        // Super admin cannot edit other super admins UNLESS they created them
        if ($currentUser->isSuperAdmin() && $user->isSuperAdmin() && $user->id !== $currentUser->id) {
            if ($user->created_by !== $currentUser->id) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'You can only edit Super Admin users that you created');
            }
        }

        // Admin can assign user or admin role
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            $roles = Role::where('guard_name', 'web')
                ->whereIn('name', ['user', 'admin'])
                ->get();
        } else {
            // Super admin can see all roles
            $roles = Role::where('guard_name', 'web')->get();
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $currentUser = Auth::user();

        // Admin cannot update super admin or other admins
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'You can only update User role accounts');
            }
        }

        // Super admin cannot update other super admins UNLESS they created them
        if ($currentUser->isSuperAdmin() && $user->isSuperAdmin() && $user->id !== $currentUser->id) {
            if ($user->created_by !== $currentUser->id) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'You can only update Super Admin users that you created');
            }
        }

        // Admin can assign user or admin role
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            if (!in_array($request->role, ['user', 'admin'])) {
                return back()
                    ->withInput()
                    ->with('error', 'You can only assign User or Admin role');
            }
        }

        // No restriction for super admin on role assignment

        $validated = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s\'-]+$/'],
            'username' => 'required|string|alpha_dash|max:255|unique:users,username,' . $user->id,
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id, 'not_regex:/\+/'],
            'role'     => 'required|exists:roles,name',
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
            'email.not_regex' => 'Please enter a valid standard email address.',
        ]);

        DB::transaction(function () use ($request, $validated, $user) {

            $user->update([
                'name'     => trim($validated['name']),
                'username' => strtolower(trim($validated['username'])),
                'email'    => strtolower(trim($validated['email'])),
            ]);

            if ($request->filled('password')) {
                $request->validate([
                    'password' => ['confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/'],
                ], [
                    'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (symbol).',
                ]);

                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Self role protection
            if ($user->id !== Auth::id()) {
                $user->syncRoles([$validated['role']]);
            }
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();

        if ($user->id === Auth::id()) {
            return request()->ajax()
                ? response()->json(['error' => 'You cannot delete yourself'], 422)
                : back()->with('error', 'You cannot delete yourself');
        }

        // Check permissions for Super Admin hierarchy
        if ($currentUser->isSuperAdmin() && $user->isSuperAdmin() && $user->id !== $currentUser->id) {
            if ($user->created_by !== $currentUser->id) {
                return request()->ajax()
                    ? response()->json(['error' => 'You can only delete Super Admin users that you created'], 403)
                    : back()->with('error', 'You can only delete Super Admin users that you created');
            }
        }

        // Check permissions for Admin
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->isAdmin())) {
             return request()->ajax()
                ? response()->json(['error' => 'Unauthorized'], 403)
                : back()->with('error', 'You can only delete User role accounts');
        }

        $user->update(['deleted_by' => Auth::id()]);
        $user->delete();

        if (request()->ajax()) {
            return response()->json(['success' => 'User moved to trash']);
        }

        return back()->with('success', 'User moved to trash');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $currentUser = Auth::user();

        // Super Admin hierarchy check
        if ($currentUser->isSuperAdmin() && $user->isSuperAdmin() && $user->id !== $currentUser->id) {
            if ($user->created_by !== $currentUser->id) {
                return request()->ajax()
                    ? response()->json(['error' => 'You can only restore Super Admin users that you created'], 403)
                    : back()->with('error', 'You can only restore Super Admin users that you created');
            }
        }

        // Admin check
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->isAdmin())) {
            return request()->ajax()
                ? response()->json(['error' => 'Unauthorized'], 403)
                : back()->with('error', 'You can only restore User role accounts');
        }

        $user->restore();
        $user->update(['deleted_by' => null]);

        if (request()->ajax()) {
            return response()->json(['success' => 'User restored successfully']);
        }

        return back()->with('success', 'User restored successfully');
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $currentUser = Auth::user();

        if ($user->id === Auth::id()) {
            return request()->ajax()
                ? response()->json(['error' => 'You cannot delete yourself'], 422)
                : back()->with('error', 'You cannot delete yourself');
        }

        // Super Admin hierarchy check
        if ($currentUser->isSuperAdmin() && $user->isSuperAdmin() && $user->id !== $currentUser->id) {
            if ($user->created_by !== $currentUser->id) {
                return request()->ajax()
                    ? response()->json(['error' => 'You can only permanently delete Super Admin users that you created'], 403)
                    : back()->with('error', 'You can only permanently delete Super Admin users that you created');
            }
        }

        // Admin check
        if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->isAdmin())) {
            return request()->ajax()
                ? response()->json(['error' => 'Unauthorized'], 403)
                : back()->with('error', 'You can only permanently delete User role accounts');
        }

        $user->roles()->detach();
        $user->forceDelete();

        if (request()->ajax()) {
            return response()->json(['success' => 'User permanently deleted']);
        }

        return back()->with('success', 'User permanently deleted');
    }

    public function removeStrike(Request $request, $userId, $strikeId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        
        $strike = \App\Models\UserStrike::where('user_id', $user->id)
            ->findOrFail($strikeId);
            
        $strike->delete();
        
        return redirect()->back()->with('success', 'User strike removed successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string|in:delete,restore,force_delete'
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $currentUser = Auth::user();

        // Prevent self action
        if (in_array($currentUser->id, $ids)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot perform bulk actions on your own account.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $users = User::withTrashed()->whereIn('id', $ids)->get();

            foreach ($users as $user) {
                // Check permissions
                if ($currentUser->isSuperAdmin() && $user->isSuperAdmin() && $user->id !== $currentUser->id) {
                    if ($user->created_by !== $currentUser->id) {
                        throw new \Exception("You don't have permission to modify Super Admin {$user->name}");
                    }
                }
                if ($currentUser->isAdmin() && !$currentUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->isAdmin())) {
                    throw new \Exception("You don't have permission to modify User {$user->name}");
                }

                if ($action === 'delete') {
                    $user->update(['deleted_by' => Auth::id()]);
                    $user->delete();
                } elseif ($action === 'restore') {
                    $user->restore();
                    $user->update(['deleted_by' => null]);
                } elseif ($action === 'force_delete') {
                    $user->roles()->detach();
                    $user->forceDelete();
                }
            }

            DB::commit();

            $actionMessage = '';
            if ($action === 'delete') $actionMessage = 'suspended';
            if ($action === 'restore') $actionMessage = 'restored';
            if ($action === 'force_delete') $actionMessage = 'permanently deleted';

            return response()->json([
                'success' => true,
                'message' => count($ids) . " user(s) have been successfully {$actionMessage}."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
