<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ModuleOwnerMiddleware
{
    public function handle(Request $request, Closure $next, string ...$departments)
    {
        $user = $request->user();
        if (!$user) {
            throw new AccessDeniedHttpException('No autenticado.');
        }

        if ($user->active === false) {
            throw new AccessDeniedHttpException('Usuario bloqueado.');
        }

        if ($user->canManageOwnedDepartment(...$departments)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Solo el area dueña puede editar este modulo.');
    }
}
