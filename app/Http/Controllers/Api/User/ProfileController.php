<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // Get authenticated user's profile
    public function show(Request $request)
    {
        $user = $request->user()->load('roles');
        
        return (new UserResource($user))->additional([
            'success' => true,
        ]);
    }

    // Update profile information
    public function update(ProfileUpdateRequest $request)
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return (new UserResource($user->load('roles')))->additional([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);
    }

    // Upload avatar
    public function uploadAvatar(UploadAvatarRequest $request)
    {
        $avatarUrl = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar')
        );

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => $avatarUrl,
            ],
        ]);
    }

    // Delete avatar
    public function deleteAvatar(Request $request)
    {
        $avatarUrl = $this->userService->deleteAvatar($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Avatar removed successfully',
            'data' => [
                'avatar_url' => $avatarUrl,
            ],
        ]);
    }

    // Change password
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->userService->changePassword(
            $request->user(),
            $request->new_password
        );

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    // Get user statistics
    public function stats(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->getStatistics(),
        ]);
    }
}