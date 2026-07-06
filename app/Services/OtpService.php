<?php

namespace App\Services;

class OtpService
{
    public function send(string $phoneNumber): void
    {
        // Fixed OTP mode: no SMS is sent.
    }

    public function verify(string $code): bool
    {
        if (config('otp.bypass')) {
            return $code === config('otp.fixed_code');
        }

        return false;
    }
}
