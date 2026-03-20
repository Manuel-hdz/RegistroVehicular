<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $originalUser = $request->attributes->get('original_user');
        $user = $originalUser instanceof User ? $originalUser : $request->user();

        if ($user && $user->current_session_id && $user->current_session_id !== $request->session()->getId()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('session_conflict', 'Tu cuenta se ha iniciado en otro dispositivo. Se cerro la sesion en este equipo.');
        }

        return $next($request);
    }
}
