<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ImpersonationPreviewMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $authenticatedUser = $request->user();
        if (!$authenticatedUser) {
            return $next($request);
        }

        $request->attributes->set('original_user', $authenticatedUser);

        $previewUserId = (int) $request->session()->get('impersonation.preview_user_id', 0);
        $originUserId = (int) $request->session()->get('impersonation.origin_user_id', 0);

        if (
            $previewUserId > 0
            && $originUserId === (int) $authenticatedUser->id
            && $authenticatedUser->role === 'superadmin'
        ) {
            $previewUser = User::find($previewUserId);

            if (!$previewUser || $previewUser->active === false) {
                $request->session()->forget(['impersonation.preview_user_id', 'impersonation.origin_user_id']);
                return redirect()->route('public.dashboard')->with('status', 'La vista seleccionada ya no esta disponible.');
            }

            $request->attributes->set('preview_user', $previewUser);
            $request->setUserResolver(fn () => $previewUser);
            Auth::setUser($previewUser);

            if (
                !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)
                && !$request->routeIs('impersonation.start')
                && !$request->routeIs('impersonation.stop')
                && !$request->routeIs('logout')
            ) {
                throw new AccessDeniedHttpException('La vista como otro usuario es solo de lectura.');
            }
        }

        return $next($request);
    }
}
