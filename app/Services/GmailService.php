<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailService
{
    protected string $clientId;

    protected string $clientSecret;

    protected string $redirectUri;

    protected ?string $refreshToken;

    public function __construct()
    {
        $this->clientId = trim((string) (config('services.gmail.client_id') ?? env('GMAIL_CLIENT_ID', '')));
        $this->clientSecret = trim((string) (config('services.gmail.client_secret') ?? env('GMAIL_CLIENT_SECRET', '')));
        $this->redirectUri = trim((string) (config('services.gmail.redirect_uri') ?? 'http://localhost:8000/oauth/gmail/callback'));
        $this->refreshToken = trim((string) (config('services.gmail.refresh_token') ?: env('GMAIL_REFRESH_TOKEN', '')));
    }

    /**
     * Generate the official Google OAuth authorization URL for Gmail sending scope.
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/gmail.send email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Exchange the authorization code from Google callback for access & refresh tokens.
     *
     * @return array{success: bool, refresh_token?: string, access_token?: string, error?: string}
     */
    public function exchangeCodeForTokens(string $code): array
    {
        try {
            $cleanCode = trim(urldecode($code));

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $cleanCode,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $refreshToken = $data['refresh_token'] ?? null;
                $accessToken = $data['access_token'] ?? null;

                if ($refreshToken) {
                    $this->updateEnvFile('GMAIL_REFRESH_TOKEN', $refreshToken);
                }

                return [
                    'success' => true,
                    'refresh_token' => $refreshToken,
                    'access_token' => $accessToken,
                ];
            }

            Log::error('Gmail OAuth Token Exchange Error: '.$response->body());

            return [
                'success' => false,
                'error' => $response->json()['error_description'] ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Gmail OAuth Exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a valid access token using the stored refresh token.
     */
    public function getAccessToken(): ?string
    {
        $refreshToken = $this->refreshToken ?: env('GMAIL_REFRESH_TOKEN');

        if (! $refreshToken) {
            return null;
        }

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'] ?? null;
            }

            Log::error('Gmail Access Token Refresh Failed: '.$response->body());

            return null;
        } catch (\Throwable $e) {
            Log::error('Gmail Token Refresh Exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Send an email via Google Gmail REST API.
     *
     * @return array{success: bool, message_id?: string, error?: string}
     */
    public function sendRawEmail(string $toEmail, string $toName, string $subject, string $htmlBody): array
    {
        $accessToken = $this->getAccessToken();

        if (! $accessToken) {
            Log::warning("Gmail API: Access token unavailable. Simulating email send to {$toEmail}");

            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Simulated: Gmail API not yet connected with refresh token.',
            ];
        }

        try {
            // Build RFC 2822 compliant email string
            $senderEmail = 'me';
            $boundary = uniqid('np_');

            $raw = "To: {$toName} <{$toEmail}>\r\n";
            $raw .= 'Subject: =?utf-8?B?'.base64_encode($subject)."?=\r\n";
            $raw .= "MIME-Version: 1.0\r\n";
            $raw .= "Content-Type: text/html; charset=utf-8\r\n";
            $raw .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $raw .= chunk_split(base64_encode($htmlBody))."\r\n";

            // URL-safe Base64 encoding
            $encodedMessage = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

            $response = Http::withToken($accessToken)
                ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                    'raw' => $encodedMessage,
                ]);

            if ($response->successful()) {
                $msgId = $response->json()['id'] ?? 'sent';
                Log::info("Gmail API successfully sent message ID: {$msgId} to {$toEmail}");

                return [
                    'success' => true,
                    'message_id' => $msgId,
                ];
            }

            Log::error('Gmail API Send Failed: '.$response->body());

            return [
                'success' => false,
                'error' => $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('Gmail Send Exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a luxury HTML Email Verification OTP code via Gmail API.
     */
    public function sendOtpEmail(string $toEmail, string $recipientName, string $otpCode): array
    {
        $subject = "Kode Verifikasi OTP Akun NusantaraMart: {$otpCode}";

        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Kode Verifikasi OTP NusantaraMart</title>
        </head>
        <body style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background-color: #FAF8F5; margin: 0; padding: 30px 15px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 540px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; overflow: hidden; border: 1px solid #EAE1D7; box-shadow: 0 4px 16px rgba(107, 66, 38, 0.06);">
                <tr>
                    <td style="background: linear-gradient(135deg, #6B4226 0%, #54321B 100%); padding: 32px 28px; text-align: center;">
                        <div style="width: 52px; height: 52px; background-color: rgba(255,255,255,0.15); border-radius: 16px; margin: 0 auto 12px; line-height: 52px; font-size: 26px;">
                            🛍️
                        </div>
                        <h1 style="color: #ffffff; font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.5px;">
                            Nusantara<span style="color: #FDECD2;">Mart</span>
                        </h1>
                        <p style="color: #E8DCCF; font-size: 12px; margin: 6px 0 0;">Marketplace Belanja Pilihan Terpercaya</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 32px 28px;">
                        <h2 style="color: #2D241E; font-size: 18px; font-weight: 700; margin: 0 0 12px;">Halo, '.htmlspecialchars($recipientName).'! 👋</h2>
                        <p style="color: #6D6D72; font-size: 14px; line-height: 1.6; margin: 0 0 24px;">
                            Terima kasih telah mendaftar di NusantaraMart. Gunakan kode verifikasi (OTP) berikut untuk memverifikasi alamat email kamu dan mengaktifkan akun:
                        </p>
                        
                        <div style="background-color: #FAF4ED; border: 2px dashed #6B4226; border-radius: 16px; padding: 20px; text-align: center; margin-bottom: 24px;">
                            <span style="font-size: 32px; font-weight: 900; letter-spacing: 8px; color: #6B4226; font-family: monospace;">'.$otpCode.'</span>
                            <p style="color: #8A7C70; font-size: 11px; margin: 8px 0 0;">⏳ Berlaku selama 10 menit. Jangan bagikan kode ini ke siapa pun.</p>
                        </div>

                        <p style="color: #8E8E93; font-size: 12px; line-height: 1.5; margin: 0;">
                            Jika kamu tidak merasa melakukan pendaftaran di NusantaraMart, kamu dapat mengabaikan email ini dengan aman.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="background-color: #FAF8F5; border-top: 1px solid #F2EAE0; padding: 20px 28px; text-align: center;">
                        <p style="color: #8A7C70; font-size: 11px; margin: 0;">© 2026 NusantaraMart Official. Hak Cipta Dilindungi Undang-Undang.</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        return $this->sendRawEmail($toEmail, $recipientName, $subject, $htmlBody);
    }

    /**
     * Helper to safely persist a key into the local .env file.
     */
    protected function updateEnvFile(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        if (preg_match("/^{$key}=/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}\n";
        }

        file_put_contents($envPath, $content);
    }
}
