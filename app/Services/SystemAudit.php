<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class SystemAudit
{
    private bool $recording = false;

    private array $pending = [];

    public function record(string $action, string $description, array $attributes = [], ?User $actor = null): void
    {
        if ($this->recording) {
            return;
        }

        $this->recording = true;
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }
            $request = app()->bound('request') ? request() : null;
            $actor ??= $request?->attributes->get('original_user') ?? $request?->user();
            $actor ??= $request?->attributes->get('audit_actor');
            $actor ??= Auth::user();
            $context = [];
            if ($request instanceof Request && $request->route()) {
                $requestId = $request->attributes->get('audit_request_id');
                if (! $requestId) {
                    $requestId = (string) Str::uuid();
                    $request->attributes->set('audit_request_id', $requestId);
                }
                $context = [
                    'request_id' => $requestId,
                    'route' => $request->route()?->getName(),
                    'method' => $request->method(),
                    'path' => Str::limit($request->path(), 500, ''),
                    'ip_address' => $request->ip(),
                ];
            }
            AuditLog::create(array_merge($context, [
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                'actor_username' => $actor?->username,
                'action' => $action,
                'description' => Str::limit($this->cleanText($description), 500, ''),
                'created_at' => now(),
            ], $this->sanitize($attributes)));
        } catch (Throwable $exception) {
            // No interrumpir la operación original ni provocar un ciclo de logs.
            error_log('No se pudo guardar la bitácora del sistema: '.$exception::class);
        } finally {
            $this->recording = false;
        }
    }

    public function modelChanged(string $action, Model $model): void
    {
        if ($model instanceof AuditLog || ! str_starts_with($model::class, 'App\\Models\\')) {
            return;
        }
        $before = $action === 'created' ? [] : $model->getRawOriginal();
        $after = $action === 'deleted' ? [] : $model->getAttributes();
        if ($action === 'updated') {
            $keys = array_keys(array_diff_key($model->getChanges(), array_flip(['updated_at', 'current_session_id', 'remember_token'])));
            if ($keys === []) {
                return;
            }
            $before = array_intersect_key($before, array_flip($keys));
            $after = array_intersect_key($after, array_flip($keys));
        }
        $this->record($action, (AuditLog::ACTIONS[$action] ?? $action).' en '.$model->getTable().' #'.$model->getKey(), [
            'subject_type' => $model->getTable(), 'subject_id' => (string) $model->getKey(),
            'before_values' => $before, 'after_values' => $after,
        ]);
    }

    public function databaseChanged(QueryExecuted $event): void
    {
        if ($this->recording || ! preg_match('/^\s*(insert(?:\s+or\s+\w+)?\s+into|replace\s+into|update|delete\s+from)\s+[`"\[]?([a-zA-Z0-9_]+)/i', $event->sql, $matches)) {
            return;
        }
        $table = $matches[2];
        if (in_array($table, ['audit_logs', 'migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'], true)) {
            return;
        }
        // Los cambios individuales se registran con sus valores en modelChanged.
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 35) as $frame) {
            if (in_array($frame['function'], ['performInsert', 'performUpdate', 'performDeleteOnModel'], true)
                && is_a($frame['class'] ?? '', Model::class, true)) {
                return;
            }
        }
        $operation = str_starts_with(strtolower($matches[1]), 'delete') ? 'delete'
            : (strtolower($matches[1]) === 'update' ? 'update' : 'insert');
        // Nunca guardar SQL ni bindings: pueden contener contraseñas o sesiones.
        if (! Schema::hasTable('audit_logs')) {
            return;
        }
        $request = app()->bound('request') ? request() : null;
        $actor = $request?->attributes->get('original_user') ?? $request?->user()
            ?? $request?->attributes->get('audit_actor') ?? Auth::user();
        // Escribir aquí alteraría PDO::lastInsertId() de insertGetId().
        // Guardar cuando la consulta ya terminó y descartar operaciones revertidas.
        $this->pending[] = [
            'connection' => $event->connectionName,
            'depth' => $event->connection->transactionLevel(),
            'action' => 'database.'.$operation,
            'description' => 'Operación de base de datos en '.$table,
            'attributes' => [
                'subject_type' => $table,
                'actor_id' => $actor?->id, 'actor_name' => $actor?->name, 'actor_username' => $actor?->username,
                'metadata' => ['operation' => $operation, 'details' => 'Operación masiva o relación; sin valores individuales.'],
                'created_at' => now()->toDateTimeString(),
            ],
        ];
    }

    public function flush(): void
    {
        $pending = $this->pending;
        $this->pending = [];
        foreach ($pending as $item) {
            $this->record($item['action'], $item['description'], $item['attributes']);
        }
    }

    public function transactionFinished(string $connection, int $depth, bool $rolledBack): void
    {
        foreach ($this->pending as $key => &$item) {
            if ($item['connection'] !== $connection) {
                continue;
            }
            if ($rolledBack && $item['depth'] > $depth) {
                unset($this->pending[$key]);
            } else {
                $item['depth'] = min($item['depth'], $depth);
            }
        }
        unset($item);
        if ($depth === 0) {
            $this->flush();
        }
    }

    public function messageLogged(MessageLogged $event): void
    {
        $context = $event->context;
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $message = 'Excepción del sistema: '.$context['exception']::class;
            $context['exception'] = $context['exception']::class;
        } else {
            $message = (string) $event->message;
        }
        $this->record('log', $message, ['metadata' => ['level' => $event->level, 'context' => $context]]);
    }

    public function sanitize(array $values): array
    {
        $result = [];
        foreach ($values as $key => $value) {
            if (preg_match('/password|passwd|secret|token|session|cookie|authorization|credential|photo_cropped/i', (string) $key)) {
                $result[$key] = '[OCULTO]';
            } elseif (is_array($value)) {
                $result[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                $result[$key] = Str::limit($this->cleanText($value), 4000, '…');
            } elseif (is_scalar($value) || $value === null) {
                $result[$key] = $value;
            } else {
                $result[$key] = '['.get_debug_type($value).']';
            }
        }

        return $result;
    }

    private function cleanText(string $value): string
    {
        if (str_contains($value, 'SQLSTATE[')) {
            return 'Error de base de datos (detalle técnico omitido).';
        }
        $value = preg_replace('/Bearer\s+\S+/i', 'Bearer [OCULTO]', $value) ?? '';
        $value = preg_replace('/((?:password|passwd|secret|token|authorization|cookie|current_session_id)[\w-]*["\x27]?\s*[:=]\s*)(?:"[^"]*"|\x27[^\x27]*\x27|\S+)/i', '$1[OCULTO]', $value) ?? '';

        return preg_replace('/data:[^;]+;base64,[A-Za-z0-9+\/=]+/', '[ARCHIVO OMITIDO]', $value) ?? '';
    }
}
