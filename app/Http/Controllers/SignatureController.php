<?php

namespace App\Http\Controllers;

use App\Models\SignatureCaptureLink;
use Google\Client as GoogleClient;
use Google\Service\Gmail as GoogleGmail;
use Google\Service\Gmail\Message as GmailMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SignatureController extends Controller
{
    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:190'],
        ]);

        $user = auth()->user();
        $targetEmail = trim((string) ($data['email'] ?? $user->email));
        if ($targetEmail === '') {
            $targetEmail = $user->email;
        }

        $plainToken = Str::random(64);
        $link = SignatureCaptureLink::create([
            'user_id' => $user->id,
            'email' => $targetEmail,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        $captureUrl = route('signature.capture.form', ['token' => $plainToken]);
        $sentError = $this->sendMailViaGmailApi(
            $user->id,
            $targetEmail,
            'CV UNAMIS - Enlace de captura de firma',
            "Captura tu firma para CV UNAMIS.\n\nAbre este enlace en tu móvil o tablet:\n{$captureUrl}\n\nEste enlace expira en 24 horas."
        );

        if ($sentError === null) {
            $link->sent_at = Carbon::now();
        } else {
            $link->sent_error = $sentError;
        }

        $link->save();

        return redirect('/dashboard')->with([
            'signature_link_url' => $captureUrl,
            'signature_link_email' => $targetEmail,
            'signature_mail_error' => $sentError,
            'ok' => $sentError
                ? 'Se generó el enlace de firma, pero no se pudo enviar correo. Copia y envía el enlace manualmente.'
                : 'Enlace de firma enviado correctamente.',
        ]);
    }

    public function showCaptureForm(string $token): View
    {
        $link = $this->resolveLink($token);
        if (! $link) {
            abort(404);
        }

        $isExpired = $link->expires_at && $link->expires_at->isPast();
        $isUsed = ! empty($link->used_at);
        $canCapture = ! $isExpired && ! $isUsed;

        return view('signature.capture', [
            'token' => $token,
            'expiresAt' => $link->expires_at,
            'email' => $link->email,
            'canCapture' => $canCapture,
            'isExpired' => $isExpired,
            'isUsed' => $isUsed,
        ]);
    }

    public function storeCapture(Request $request, string $token): RedirectResponse
    {
        $link = $this->resolveActiveLink($token);
        if (! $link) {
            return redirect()->route('signature.capture.form', ['token' => $token])
                ->with('error', 'El enlace de firma ya no es válido. Solicita uno nuevo desde tu dashboard.');
        }

        $data = $request->validate([
            'signature_data' => ['required', 'string'],
        ]);

        $raw = trim((string) $data['signature_data']);
        if (! str_starts_with($raw, 'data:image/png;base64,')) {
            return back()->with('error', 'Formato de firma inválido. Intenta nuevamente.');
        }

        $base64 = substr($raw, strlen('data:image/png;base64,'));
        $binary = base64_decode($base64, true);
        if (! is_string($binary) || strlen($binary) < 400) {
            return back()->with('error', 'La firma está vacía o incompleta. Firma nuevamente.');
        }

        $relativePath = 'signatures/user_' . $link->user_id . '.png';
        Storage::disk('local')->put($relativePath, $binary);

        $user = $link->user;
        $user->signature_file_path = $relativePath;
        $user->signature_signed_at = Carbon::now();
        $user->save();

        $link->used_at = Carbon::now();
        $link->save();

        return redirect()->route('signature.capture.form', ['token' => $token])
            ->with('ok', 'Firma capturada y guardada correctamente. Ya se usará en tus PDFs.');
    }

    private function resolveActiveLink(string $token): ?SignatureCaptureLink
    {
        $hash = hash('sha256', $token);
        return SignatureCaptureLink::query()
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();
    }

    private function resolveLink(string $token): ?SignatureCaptureLink
    {
        return SignatureCaptureLink::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    private function sendMailViaGmailApi(int $userId, string $to, string $subject, string $body): ?string
    {
        try {
            $account = DB::table('google_accounts')->where('user_id', $userId)->first();
            if (! $account) {
                return 'No existe token de Google. Cierra sesión y vuelve a entrar con Google para autorizar Gmail.';
            }

            $client = new GoogleClient();
            $client->setHttpClient(new \GuzzleHttp\Client([
                'timeout' => 10,
                'connect_timeout' => 5,
            ]));
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect'));

            $client->setAccessToken(Crypt::decryptString((string) $account->access_token));
            if ($client->isAccessTokenExpired()) {
                if (empty($account->refresh_token)) {
                    return 'El token de Google expiró y no hay refresh token. Cierra sesión y vuelve a entrar con Google.';
                }

                $refreshToken = Crypt::decryptString((string) $account->refresh_token);
                $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (isset($newToken['error'])) {
                    return 'Google rechazó refrescar token: ' . (string) $newToken['error'];
                }

                if (! empty($newToken['access_token'])) {
                    DB::table('google_accounts')->where('user_id', $userId)->update([
                        'access_token' => Crypt::encryptString((string) $newToken['access_token']),
                        'token_expires_at' => now()->addSeconds((int) ($newToken['expires_in'] ?? 3600)),
                        'updated_at' => now(),
                    ]);
                    $client->setAccessToken((string) $newToken['access_token']);
                }
            }

            $gmail = new GoogleGmail($client);
            $from = (string) (auth()->user()?->email ?? config('mail.from.address'));
            $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8');

            $raw = "From: CV UNAMIS <{$from}>\r\n";
            $raw .= "To: {$to}\r\n";
            $raw .= "Subject: {$encodedSubject}\r\n";
            $raw .= "MIME-Version: 1.0\r\n";
            $raw .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $raw .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $raw .= chunk_split(base64_encode($body));

            $msg = new GmailMessage();
            $msg->setRaw(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='));
            $gmail->users_messages->send('me', $msg);

            return null;
        } catch (\Throwable $e) {
            return mb_substr($e->getMessage(), 0, 255);
        }
    }
}
