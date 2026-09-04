<?php

namespace App\Http\Controllers;

use App\Services\GmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GmailOAuthController extends Controller
{
    public function __construct(
        protected GmailService $gmailService
    ) {}

    /**
     * Start the 1-click Google OAuth flow for Gmail API.
     */
    public function connect(): RedirectResponse
    {
        return redirect()->away($this->gmailService->getAuthUrl());
    }

    /**
     * Handle the Google OAuth redirect callback and save refresh token.
     */
    public function callback(Request $request): View
    {
        $code = $request->query('code');
        $error = $request->query('error');

        if ($error) {
            return view('oauth.gmail-result', [
                'success' => false,
                'message' => 'Otorisasi Google dibatalkan atau terjadi kesalahan: '.$error,
            ]);
        }

        if (! $code) {
            return view('oauth.gmail-result', [
                'success' => false,
                'message' => 'Kode otorisasi Google tidak ditemukan dalam permintaan.',
            ]);
        }

        $result = $this->gmailService->exchangeCodeForTokens($code);

        if ($result['success']) {
            return view('oauth.gmail-result', [
                'success' => true,
                'message' => 'Akun Gmail berhasil terhubung dengan sukses! Server sekarang siap mengirim email verifikasi OTP otomatis via Gmail API resmi. 🎉',
                'refreshToken' => $result['refresh_token'] ?? null,
            ]);
        }

        return view('oauth.gmail-result', [
            'success' => false,
            'message' => 'Gagal menukarkan kode token: '.($result['error'] ?? 'Terjadi kesalahan tidak dikenal.'),
        ]);
    }
}
