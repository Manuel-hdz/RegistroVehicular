<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->current_session_id && $user->current_session_id !== $request->session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->with('session_conflict', 'Tu cuenta se ha iniciado en otro dispositivo. Se cerró la sesión en este equipo.');
        }
        return $next($request);
    }
}

