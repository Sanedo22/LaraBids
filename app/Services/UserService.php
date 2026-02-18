<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class UserService
{
    // Update profile
    public function updateProfile(User $user, array $data): User
    {
        // Check if email changed (only if email is in the data)
        $emailChanged = isset($data['email']) && $user->email !== $data['email'];

        // Update user (exclude avatar from mass assignment)
        $user->fill(collect($data)->except('avatar')->toArray());

        // If email changed, mark as unverified
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user->fresh();
    }

    // Upload avatar and delete old one
    public function uploadAvatar(User $user, UploadedFile $avatar): string
    {
        // Delete old avatar if exists
        $this->deleteAvatarFile($user);

        // Store new avatar
        $path = $avatar->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return $user->avatar_url;
    }

    // Delete avatar
    public function deleteAvatar(User $user): string
    {
        // Delete avatar file if exists
        $this->deleteAvatarFile($user);

        $user->avatar = null;
        $user->save();

        return $user->avatar_url; // Returns default avatar URL
    }

    // Change password
    public function changePassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $user->save();
    }

    // Private helper: Delete avatar file from storage
    private function deleteAvatarFile(User $user): void
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}