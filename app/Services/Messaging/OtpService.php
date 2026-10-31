<?php

namespace App\Services\Messaging;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/** OTP over WhatsApp first (cheaper, more reliable), SMS as fallback. */
class OtpService
{
    public function __construct(protected WhatsApp $whatsapp, protected Sms $sms) {}

    public function send(string $phone, string $purpose = 'login', string $channel = 'whatsapp'): bool
    {
        $phone = WhatsApp::normalize($phone);
        $key = 'otp:'.$phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return false;
        }
        RateLimiter::hit($key, 600);

        $code = (string) random_int(100000, 999999);
        OtpCode::where('phone', $phone)->where('purpose', $purpose)->delete();
        OtpCode::create(['phone' => $phone, 'purpose' => $purpose, 'code_hash' => Hash::make($code), 'expires_at' => now()->addMinutes(10)]);

        $text = "رمز التحقق الخاص بك: {$code}\nصالح لمدة 10 دقائق. لا تشاركه مع أحد.";
        $channel === 'sms' ? $this->sms->send($phone, $text) : $this->whatsapp->send($phone, $text);

        return true;
    }

    public function verify(string $phone, string $code, string $purpose = 'login'): bool
    {
        $otp = OtpCode::where('phone', WhatsApp::normalize($phone))->where('purpose', $purpose)->latest('id')->first();
        if (! $otp || $otp->expires_at->isPast() || $otp->attempts >= 5) {
            return false;
        }
        $otp->increment('attempts');
        if (! Hash::check($code, $otp->code_hash)) {
            return false;
        }
        $otp->delete();

        return true;
    }
}
