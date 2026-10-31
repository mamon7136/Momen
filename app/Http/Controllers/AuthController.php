<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Messaging\OtpService;
use App\Services\Messaging\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** Login with phone/email + password, or phone + WhatsApp OTP. */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('platform.login');
    }

    public function sendOtp(Request $request, OtpService $otp)
    {
        $request->validate(['phone' => 'required|string']);
        $phone = WhatsApp::normalize($request->phone);
        if (User::where('phone', $phone)->exists()) {
            $otp->send($phone, 'login', $request->input('channel', 'whatsapp'));
        }

        return back()->withInput()->with('otp_sent', true)->with('status', 'إذا كان الرقم مسجلاً فسيصلك رمز التحقق عبر واتساب');
    }

    public function login(Request $request, OtpService $otp)
    {
        $data = $request->validate(['login' => 'required|string', 'password' => 'nullable|string', 'otp' => 'nullable|digits:6']);
        $login = trim($data['login']);
        $field = str_contains($login, '@') ? 'email' : 'phone';
        $value = $field === 'phone' ? WhatsApp::normalize($login) : strtolower($login);

        $ok = false;
        if (! empty($data['otp']) && $field === 'phone' && $otp->verify($value, $data['otp'])) {
            $user = User::where('phone', $value)->first();
            if ($user) {
                Auth::login($user, true);
                $ok = true;
            }
        } elseif (! empty($data['password'])) {
            $ok = Auth::attempt([$field => $value, 'password' => $data['password']], $request->boolean('remember'));
        }

        if (! $ok) {
            throw ValidationException::withMessages(['login' => 'بيانات الدخول غير صحيحة']);
        }
        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->is_super_admin ? route('admin.home') : route('dashboard.home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
