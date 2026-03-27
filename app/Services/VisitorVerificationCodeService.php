<?php

namespace App\Services;

use App\Mail\VisitorVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VisitorVerificationCodeService
{
    private const EXPIRES_MINUTES = 15;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function sendCode(User $user, bool $force = false): array
    {
        if ($user->hasVerifiedEmail()) {
            return ['ok' => true, 'code' => 'already_verified'];
        }

        if (!$force && $this->isInCooldown($user)) {
            $remaining = max(1, self::RESEND_COOLDOWN_SECONDS - now()->diffInSeconds($user->email_verification_sent_at));

            return [
                'ok' => false,
                'code' => 'cooldown',
                'message' => 'Please wait ' . $remaining . ' seconds before requesting another code.',
            ];
        }

        $plainCode = $this->generateCode();

        $user->forceFill([
            'email_verification_code' => Hash::make($plainCode),
            'email_verification_code_expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'email_verification_sent_at' => now(),
        ])->save();

        try {
            Mail::to($user->email)->send(new VisitorVerificationCodeMail(
                $user,
                $plainCode,
                self::EXPIRES_MINUTES
            ));
        } catch (\Throwable $e) {
            Log::error('Failed sending visitor verification code', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'code' => 'mail_failed',
                'message' => 'We could not send the verification email right now. Please try again.',
            ];
        }

        return ['ok' => true, 'code' => 'sent'];
    }

    public function verify(User $user, string $code): array
    {
        if ($user->hasVerifiedEmail()) {
            return ['ok' => true, 'code' => 'already_verified'];
        }

        if (blank($user->email_verification_code) || !$user->email_verification_code_expires_at) {
            return [
                'ok' => false,
                'code' => 'missing_code',
                'message' => 'No active verification code was found. Please request a new code.',
            ];
        }

        if ($user->email_verification_code_expires_at->isPast()) {
            return [
                'ok' => false,
                'code' => 'expired',
                'message' => 'Your verification code has expired. Please request a new one.',
            ];
        }

        if (!Hash::check($code, $user->email_verification_code)) {
            return [
                'ok' => false,
                'code' => 'invalid_code',
                'message' => 'The verification code you entered is invalid.',
            ];
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
            'email_verification_sent_at' => null,
            'status' => $user->status ?: 'Active',
        ])->save();

        return ['ok' => true, 'code' => 'verified'];
    }

    public function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return $email;
        }

        $maskedName = strlen($name) <= 2
            ? substr($name, 0, 1) . '*'
            : substr($name, 0, 2) . str_repeat('*', max(1, strlen($name) - 2));

        return $maskedName . '@' . $domain;
    }

    public function getCooldownRemaining(User $user): int
    {
        if (!$this->isInCooldown($user)) {
            return 0;
        }

        return max(0, self::RESEND_COOLDOWN_SECONDS - now()->diffInSeconds($user->email_verification_sent_at));
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function isInCooldown(User $user): bool
    {
        return $user->email_verification_sent_at
            && now()->diffInSeconds($user->email_verification_sent_at) < self::RESEND_COOLDOWN_SECONDS;
    }
}
