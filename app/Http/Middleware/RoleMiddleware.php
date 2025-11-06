<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            throw new AccessDeniedHttpException('No autenticado.');
        }

        if ($user->active === false) {
            throw new AccessDeniedHttpException('Usuario bloqueado.');
        }

        // SuperAdmin siempre permite
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Sin permisos para esta acción.');
    }
}

