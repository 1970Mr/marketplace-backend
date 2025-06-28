<?php

namespace App\Services\Panel\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function updateProfileHandler(User $user, array $data): User
    {
        $user->name = $data['name'] ?? $user->name;
        $user->country_name = $data['country_name'] ?? $user->country_name;
        $user->company_name = $data['company_name'] ?? $user->company_name;
        $user->phone_number = $data['phone_number'] ?? $user->phone_number;

        if (!empty($data['remove_avatar'])) {
            $this->removeAvatar($user);
        }

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $this->uploadAvatar($user, $data['avatar']);
        }

        $user->save();
        return $user;
    }

    public function changeEmailHandler(User $user, array $data): User
    {
        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'Current password is incorrect']);
        }

        $user->email = $data['email'];
        $user->save();

        return $user;
    }

    public function changePasswordHandler(User $user, array $data): User
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return $user;
    }

    private function removeAvatar(User $user): void
    {
        if (!empty($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
        }
    }

    private function uploadAvatar(User $user, UploadedFile $file): void
    {
        $this->removeAvatar($user);
        $path = $file->store('profile/avatars', 'public');
        $user->avatar = $path;
    }
}

