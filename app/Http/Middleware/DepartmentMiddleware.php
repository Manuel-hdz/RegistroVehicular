<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DepartmentMiddleware
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

        $currentDepartment = $this->normalize((string) $user->department);
        $allowedDepartments = array_map(fn (string $department): string => $this->normalize($department), $departments);

        if ($allowedDepartments === [] || in_array($currentDepartment, $allowedDepartments, true)) {
            return $next($request);
        }

        throw new AccessDeniedHttpException('Sin permisos para este departamento.');
    }

    private function normalize(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = mb_strtolower($value, 'UTF-8');

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }
}
