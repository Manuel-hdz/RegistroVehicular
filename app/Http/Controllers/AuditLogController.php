<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->role === 'superadmin', 403);
        $filters = $request->validate([
            'actor_id' => ['nullable', 'integer', 'min:0'],
            'action' => ['nullable', Rule::in(array_keys(AuditLog::ACTIONS))],
            'subject_type' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('date_from') ? 'after_or_equal:date_from' : null]),
            'search' => ['nullable', 'string', 'max:150'],
        ]);
        $query = AuditLog::query();
        if ($request->filled('actor_id')) {
            (int) $filters['actor_id'] === 0 ? $query->whereNull('actor_id') : $query->where('actor_id', $filters['actor_id']);
        }
        foreach (['action', 'subject_type'] as $key) {
            if ($request->filled($key)) {
                $query->where($key, $filters[$key]);
            }
        }
        $timezone = config('audit.timezone');
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', CarbonImmutable::parse($filters['date_from'], $timezone)->startOfDay()->utc());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', CarbonImmutable::parse($filters['date_to'], $timezone)->endOfDay()->utc());
        }
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($query) use ($search): void {
                foreach (['description', 'actor_name', 'actor_username', 'subject_id', 'path', 'route'] as $field) {
                    $query->orWhere($field, 'like', '%'.$search.'%');
                }
            });
        }

        return view('audit-logs.index', [
            'logs' => $query->orderByDesc('id')->paginate(30)->withQueryString(),
            'actors' => AuditLog::whereNotNull('actor_id')->select('actor_id')
                ->selectRaw('MAX(actor_name) as actor_name, MAX(actor_username) as actor_username')
                ->groupBy('actor_id')->orderBy('actor_name')->get(),
            'subjects' => AuditLog::whereNotNull('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type'),
            'actions' => AuditLog::ACTIONS,
            'timezone' => $timezone,
        ]);
    }
}
