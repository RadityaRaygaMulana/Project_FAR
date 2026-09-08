<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\GmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle user login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim($credentials['login']);
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $remember = $request->boolean('remember');

        // Primary attempt based on format (case-insensitive)
        $primaryCredentials = [
            $isEmail ? 'email' : 'username' => strtolower($loginInput),
            'password' => $credentials['password'],
        ];

        // Secondary fallback attempt to allow maximum flexibility
        $secondaryCredentials = [
            $isEmail ? 'username' : 'email' => strtolower($loginInput),
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($primaryCredentials, $remember) || Auth::attempt($secondaryCredentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            AuditLogger::auth('Login Sukses', 'Pengguna berhasil masuk ke sistem', $user, $request);

            // Admin accounts completely bypass OTP verification
            if ($user->isAdmin()) {
                if (! $user->email_verified_at) {
                    $user->forceFill(['email_verified_at' => now(), 'otp_code' => null, 'otp_expires_at' => null])->save();
                }

                return redirect()->route('home')
                    ->with('success', 'Selamat datang kembali, '.$user->name.'! ⚡');
            }

            if (! $user->email_verified_at) {
                return redirect()->route('verification.notice')
                    ->with('info', 'Silakan masukkan kode OTP yang telah dikirimkan ke email kamu untuk mengaktifkan akun.');
            }

            return redirect()->route('home')
                ->with('success', 'Selamat datang kembali, '.$user->name.'! 🍿');
        }

        AuditLogger::auth('Login Gagal', "Percobaan login gagal dengan identitas '{$loginInput}'", null, $request);

        return back()->withErrors([
            'login' => 'Username/Email atau password yang kamu masukkan salah.',
        ])->onlyInput('login');
    }

    public function __construct(
        protected GmailService $gmailService
    ) {}

    /**
     * Show registration form.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Handle user registration and dispatch OTP via Gmail API.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $otpCode = sprintf('%06d', mt_rand(100000, 999999));

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10),
            'email_verified_at' => null,
        ]);

        // Send OTP via Gmail API
        $this->gmailService->sendOtpEmail($user->email, $user->name, $otpCode);

        AuditLogger::auth('Pendaftaran Akun Baru', "Akun terdaftar dengan username '{$user->username}' (menunggu verifikasi OTP)", $user, $request);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('success', 'Kode verifikasi OTP 6-digit telah dikirim ke email '.$user->email.'! Silakan periksa inbox kamu. 📬');
    }

    /**
     * Show OTP Verification screen.
     */
    public function showVerifyEmail(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return redirect()->route('home');
        }

        return view('auth.verify-email', compact('user'));
    }

    /**
     * Verify user email with submitted 6-digit OTP code.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->otp_code !== $request->otp) {
            AuditLogger::auth('Verifikasi OTP Gagal', 'Kode OTP salah dimasukkan', $user, $request);

            return back()->withErrors(['otp' => 'Kode OTP yang kamu masukkan salah. Silakan periksa kembali.']);
        }

        if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
            AuditLogger::auth('Verifikasi OTP Kedaluwarsa', 'Kode OTP sudah melewati batas waktu', $user, $request);

            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan klik tombol "Kirim Ulang Kode OTP".']);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        AuditLogger::auth('Verifikasi Email Sukses', 'Email akun berhasil diverifikasi dengan OTP', $user, $request);

        return redirect()->route('home')
            ->with('success', 'Email kamu berhasil diverifikasi! Selamat datang di NusantaraMart! 🎉🛍️');
    }

    /**
     * Resend a fresh OTP verification code via Gmail API.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return redirect()->route('home');
        }

        $otpCode = sprintf('%06d', mt_rand(100000, 999999));

        $user->forceFill([
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        $this->gmailService->sendOtpEmail($user->email, $user->name, $otpCode);

        AuditLogger::auth('Kirim Ulang OTP', "Kode OTP baru dikirimkan ke {$user->email}", $user, $request);

        return back()->with('success', 'Kode OTP baru telah berhasil dikirim ke '.$user->email.'! 🚀');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLogger::auth('Logout', 'Pengguna keluar dari sesi aplikasi', $user, $request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Kamu telah keluar. Sampai jumpa lagi! 🍫');
    }
}
