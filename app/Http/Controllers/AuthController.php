<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Verifica si el usuario está activo antes de autenticar
        $user = \App\Models\User::where('username', $credentials['username'])->first();
        if ($user && $user->active === false) {
            app(\App\Services\SystemAudit::class)->record('login_failed', 'Acceso rechazado: usuario inactivo.', [], $user);
            return back()->withErrors(['username' => 'Usuario bloqueado. Contacte a sistemas.'])->onlyInput('username');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (Auth::user()->active === false) {
                Auth::logout();
                return back()->withErrors(['username' => 'Usuario bloqueado. Contacte a sistemas.'])->onlyInput('username');
            }
            $request->session()->regenerate();
            // Guardar ID de sesión actual para forzar una sola sesión activa
            $user = Auth::user();
            $user->current_session_id = $request->session()->getId();
            $user->save();
            return redirect()->intended(route('public.dashboard'));
        }
        
        return back()->withErrors([
            'username' => 'Credenciales inválidas.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
