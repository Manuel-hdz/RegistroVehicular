<?php

namespace App\Http\Middleware;

use App\Services\SystemAudit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditRequest
{
    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('audit_request_id', (string) Str::uuid());
        $request->attributes->set('audit_actor', $request->user());
        $started = microtime(true);
        $exceptionType = null;
        try {
            $response = $next($request);
            $status = $response->getStatusCode();

            return $response;
        } catch (Throwable $exception) {
            $exceptionType = $exception::class;
            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode()
                : ($exception instanceof ValidationException ? 422 : 500);
            throw $exception;
        } finally {
            app(SystemAudit::class)->flush();
            app(SystemAudit::class)->record('request', $request->method().' '.($request->route()?->getName() ?? $request->path()), [
                'status_code' => $status ?? 500,
                'metadata' => [
                    'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                    'exception_type' => $exceptionType,
                    'preview_user_id' => $request->attributes->get('preview_user')?->id,
                ],
            ]);
        }
    }
}
