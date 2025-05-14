<?php

namespace App\Services\Admin\Auth;

use App\Http\Requests\V1\Auth\LoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function checkAuth(LoginRequest $request): string
    {
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $admin->createToken('auth_token')->plainTextToken;
    }
}
