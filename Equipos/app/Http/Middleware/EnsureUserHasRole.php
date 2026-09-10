<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $roles = array_slice(func_get_args(), 2);

        if (! $user) {
            abort(403);
        }

        if ($user->isSuperadministrador() || $user->hasRole(...$roles)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para entrar a esta seccion.');
    }
}
