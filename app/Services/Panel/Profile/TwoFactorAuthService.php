<?php

namespace App\Services\Panel\Profile;

use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function enableTwoFactor(User $user): array
    {
        if ($user->twoFactorToken && $user->twoFactorToken->isConfirmed()) {
            throw ValidationException::withMessages([
                'two_factor' => 'Two-factor authentication is already enabled.',
            ]);
        }

        // Generate new secret and recovery codes
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = json_encode($this->generateRecoveryCodes());

        // Create or update 2FA token
        $user->twoFactorToken()->updateOrCreate([], [
            'secret' => encrypt($secret),
            'recovery_codes' => encrypt($recoveryCodes),
        ]);

        return ['secret' => $secret];
    }

    public function disableTwoFactor(User $user): void
    {
        if (!$user->twoFactorToken) {
            throw ValidationException::withMessages([
                'two_factor' => 'Two-factor authentication is not enabled.',
            ]);
        }

        $user->twoFactorToken->delete();
    }

    public function getTwoFactorQrCode(User $user): array
    {
        $token = $user->twoFactorToken;

        if (!$token) {
            throw ValidationException::withMessages([
                'two_factor' => 'Two-factor authentication is not enabled.',
            ]);
        }

        $secret = decrypt($token->secret);
        $otpUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Generate SVG QR Code
        $svg = (string) QrCode::size(300)->generate($otpUrl);

        return [
            'passkey' => $secret,
            'qr_code' => $svg,
        ];
    }

    public function verifyTwoFactor(User $user, string $code): void
    {
        $token = $user->twoFactorToken;

        if (!$token) {
            throw ValidationException::withMessages([
                'two_factor' => 'Two-factor authentication is not enabled.',
            ]);
        }

        $secret = decrypt($token->secret);

        if (!$this->google2fa->verifyKey($secret, $code)) {
            throw ValidationException::withMessages([
                'code' => 'Invalid two-factor authentication code.',
            ]);
        }

        // Confirm 2FA
        $token->update(['confirmed_at' => now()]);
    }

    public function getRecoveryCodes(User $user): array
    {
        $token = $user->twoFactorToken;

        if (!$token || !$token->isConfirmed()) {
            throw ValidationException::withMessages([
                'two_factor' => 'Two-factor authentication is not enabled.',
            ]);
        }

        return json_decode(decrypt($token->recovery_codes), true);
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }
}
