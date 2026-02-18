<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(): RedirectResponse
    {
        return redirect('/login')->withErrors([
            'email' => 'El acceso local está deshabilitado. Usa Google o Microsoft.',
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect('/login')->withErrors([
                'email' => $this->friendlyGoogleOAuthError(
                    (string) $request->query('error'),
                    (string) $request->query('error_description', '')
                ),
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable) {
            return redirect('/login')->withErrors([
                'email' => 'No fue posible autenticar con Google. Verifica la configuración OAuth (Client ID, Secret y Redirect URI) e inténtalo nuevamente.',
            ]);
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        if ($email === '') {
            return redirect('/login')->withErrors([
                'email' => 'Google no devolvió un correo válido.',
            ]);
        }

        $name = trim((string) ($googleUser->getName() ?: 'Usuario'));

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'role' => 'usuario',
                'active' => true,
            ]);
        } elseif ($user->name !== $name && $name !== '') {
            $user->name = $name;
            $user->save();
        }

        if (! (bool) $user->active) {
            return redirect('/login')->withErrors([
                'email' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function friendlyGoogleOAuthError(string $error, string $description = ''): string
    {
        $desc = Str::lower($description);

        if ($error === 'access_denied') {
            return 'Acceso cancelado en Google. Si deseas entrar, autoriza la cuenta e inténtalo de nuevo.';
        }

        if ($error === 'invalid_request' && str_contains($desc, 'redirect_uri')) {
            return 'Configuración OAuth inválida: falta o no coincide el Redirect URI en Google Cloud.';
        }

        if ($error === 'invalid_client') {
            return 'Configuración OAuth inválida: revisa GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en el archivo .env.';
        }

        if ($error === 'unauthorized_client') {
            return 'La app OAuth no está autorizada para esta cuenta. Revisa el tipo de usuario y estado en Google Auth Platform.';
        }

        if ($error === 'invalid_scope') {
            return 'La app está solicitando permisos inválidos. Revisa los scopes de Google OAuth.';
        }

        return 'Error de autenticación con Google. Revisa la configuración OAuth e inténtalo nuevamente.';
    }
}
