<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->getDashboardRoute());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Rate limiting
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300);
            $this->logLoginAttempt($request, $user, false, 'Invalid credentials');

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (in_array($user->status, ['inactive', 'suspended'])) {
            throw ValidationException::withMessages([
                'email' => 'Your account is ' . $user->status . '. Please contact admin.',
            ]);
        }

        // Check 2FA
        if ($user->two_factor_enabled) {
            session(['2fa_user_id' => $user->id, '2fa_remember' => $request->boolean('remember')]);
            return redirect()->route('auth.2fa');
        }

        Auth::login($user, $request->boolean('remember'));
        RateLimiter::clear($key);

        $this->updateLoginInfo($request, $user);
        $this->logLoginAttempt($request, $user, true);

        return redirect()->intended($user->getDashboardRoute());
    }

    public function showTwoFactor()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $userId = session('2fa_user_id');
        $user = User::findOrFail($userId);

        // Verify TOTP code (using Google Authenticator compatible library)
        $valid = $this->verifyTotpCode($user->two_factor_secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        Auth::login($user, session('2fa_remember', false));
        session()->forget(['2fa_user_id', '2fa_remember']);

        $this->updateLoginInfo($request, $user);
        $this->logLoginAttempt($request, $user, true);

        return redirect($user->getDashboardRoute());
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['is_online' => false]);

            LoginHistory::where('user_id', $user->id)
                ->whereNull('logged_out_at')
                ->latest()
                ->first()
                ?->update(['logged_out_at' => now()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function updateLoginInfo(Request $request, User $user): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'is_online' => true,
        ]);
    }

    private function logLoginAttempt(Request $request, ?User $user, bool $success, string $reason = ''): void
    {
        if (!$user) return;

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $agent->device() ?: 'Unknown',
            'platform' => $agent->platform() ?: 'Unknown',
            'browser' => $agent->browser() ?: 'Unknown',
            'is_successful' => $success,
            'failure_reason' => $reason,
        ]);
    }

    private function verifyTotpCode(string $secret, string $code): bool
    {
        // Simple TOTP verification - in production use a proper TOTP library
        // like pragmarx/google2fa
        $timestamp = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            $computedCode = $this->generateTotpCode($secret, $timestamp + $i);
            if ($computedCode === $code) {
                return true;
            }
        }
        return false;
    }

    private function generateTotpCode(string $secret, int $timestamp): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $binaryKey = '';

        for ($i = 0; $i < strlen($secret); $i++) {
            $pos = strpos($base32Chars, $secret[$i]);
            if ($pos !== false) {
                $binaryKey .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }

        $binaryKey = str_split($binaryKey, 8);
        $secretKey = array_map('bindec', array_slice($binaryKey, 0, count($binaryKey)));
        $secretKey = array_map('chr', $secretKey);
        $secretKey = implode('', $secretKey);

        $timeBytes = pack('N*', 0) . pack('N*', $timestamp);
        $hash = hash_hmac('sha1', $timeBytes, $secretKey, true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
}
