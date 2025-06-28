<?php

namespace App\Services\Auth;

use App\Http\Requests\V1\Auth\RegisterRequest;
use App\Models\TwoFactorToken;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Stevebauman\Location\Facades\Location;

class AuthService
{
    public function __construct(private readonly Google2FA $google2fa) {}

    public function createUser(RegisterRequest $request): User
    {
        // Get the client IP address
        $ipAddress = $request->ip();
        $location = Location::get($ipAddress);

        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country_name' => $location->countryName ?? null,
        ]);
    }

    public function checkAuth($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if 2FA is enabled for this user
        $twoFactorToken = $user->twoFactorToken;
        if ($twoFactorToken && $twoFactorToken->isConfirmed()) {
            $tempToken = bin2hex(random_bytes(32));
            $twoFactorToken->update(['temp_token' => $tempToken]);
            return $tempToken;
        }

        // Normal token if no 2FA is required
        return $user->createToken('auth_token')->plainTextToken;
    }

    public function sendPasswordResetNotification(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        $token = Password::createToken($user);

        $user->sendPasswordResetNotification($token);
    }

    public function resetPassword(array $params): void
    {
        $status = Password::reset(
            $params,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                // Revoke all tokens to force re-login
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }

    public function verifyEmailHandler(array $params): array
    {
        if (auth()->id() != $params['id']) {
            throw ValidationException::withMessages([
                'id' => 'Invalid user id.',
            ]);
        }

        $user = User::findOrFail($params['id']);
        if ($user->email !== $params['email']) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email address.',
            ]);
        }

        if (!hash_equals($params['hash'], sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'hash' => 'Invalid verification hash.',
            ]);
        }

        // Check if the email is already verified
        if ($user->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified',
            ];
        }

        // Mark the email as verified
        $user->markEmailAsVerified();

        return [
            'message' => 'Email verified successfully',
        ];
    }

    public function sendVerificationEmailHandler(Request $request): array
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified',
            ];
        }

        URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())]
        );

        $user->notify(new EmailVerificationNotification(
            $user->getKey(),
            $user->getEmailForVerification(),
            sha1($user->getEmailForVerification())
        ));

        return [
            'message' => 'Verification link sent',
        ];
    }

    public function verify2FACode(string $token, string $code): string
    {
        $twoFactorToken = TwoFactorToken::where('temp_token', $token)->first();

        if (!$twoFactorToken) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired token.'],
            ]);
        }

        $secret = decrypt($twoFactorToken->secret);

        if (!$this->google2fa->verifyKey($secret, $code)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid 2FA code.'],
            ]);
        }

        // Clear temp token after successful verification
        $twoFactorToken->resetTempToken();

        return $twoFactorToken->user->createToken('auth_token')->plainTextToken;
    }
}
