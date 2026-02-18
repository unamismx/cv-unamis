<?php

namespace App\Http\Controllers;

use App\Models\SignatureCaptureLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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
        $sentError = null;

        try {
            Mail::raw(
                "Captura tu firma para CV UNAMIS.\n\nAbre este enlace en tu móvil o tablet:\n{$captureUrl}\n\nEste enlace expira en 24 horas.",
                function ($message) use ($targetEmail) {
                    $message->to($targetEmail)->subject('CV UNAMIS - Enlace de captura de firma');
                }
            );
            $link->sent_at = Carbon::now();
        } catch (\Throwable $e) {
            $sentError = mb_substr($e->getMessage(), 0, 255);
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

        $relativePath = 'private/signatures/user_' . $link->user_id . '.png';
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
}
