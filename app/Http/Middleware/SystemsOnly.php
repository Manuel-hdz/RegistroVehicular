<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SystemsOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            throw new AccessDeniedHttpException('No autenticado.');
        }

        if ($user->active === false) {
            throw new AccessDeniedHttpException('Usuario bloqueado.');
        }

        if (!$user->isSystemsUser()) {
            throw new AccessDeniedHttpException('Sin permisos para esta seccion.');
        }

        return $next($request);
    }
}
