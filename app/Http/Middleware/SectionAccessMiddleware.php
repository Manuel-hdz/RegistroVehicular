<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SectionAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $section)
    {
        $user = $request->user();
        if (!$user) {
            throw new AccessDeniedHttpException('No autenticado.');
        }

        if ($user->active === false) {
            throw new AccessDeniedHttpException('Usuario bloqueado.');
        }

        if ($user->canAccessSection($section)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Sin permisos para esta seccion.');
    }
}
