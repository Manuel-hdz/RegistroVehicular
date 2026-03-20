<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderBy('name');
        $departments = ['' => 'Todos'] + $this->departments();

        if ($request->filled('department')) {
            $query->where('department', $request->string('department'));
        }

        $users = $query->paginate(20)->appends($request->query());
        $selectedUser = null;
        $selectedUserId = (int) $request->input('selected_user');
        if ($selectedUserId > 0) {
            $selectedUser = User::find($selectedUserId);
        }

        return view('users.index', [
            'users' => $users,
            'departments' => $departments,
            'selectedUser' => $selectedUser,
            'moduleOptions' => User::modulePermissionLabels(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => $this->roles(),
            'departments' => $this->departments(),
            'moduleOptions' => User::modulePermissionLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:191', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'department' => ['required', Rule::in(array_keys($this->departments()))],
            'special_permissions' => ['nullable', 'boolean'],
            'module_permissions' => ['nullable', 'array'],
            'module_permissions.*' => ['string', Rule::in(array_keys(User::modulePermissionLabels()))],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');
        $data['password'] = bcrypt($data['password']);
        $data['module_permissions'] = $request->has('special_permissions')
            ? collect($request->input('module_permissions', []))->map(fn ($permission) => (string) $permission)->unique()->values()->all()
            : [];
        unset($data['special_permissions']);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
            'departments' => $this->departments(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:191', 'unique:users,username,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'department' => ['required', Rule::in(array_keys($this->departments()))],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index', array_filter([
            'page' => $request->input('page'),
        ]))->with('status', 'Usuario actualizado.');
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'module_permissions' => ['nullable', 'array'],
            'module_permissions.*' => ['string', Rule::in(array_keys(User::modulePermissionLabels()))],
            'page' => ['nullable', 'integer'],
            'department' => ['nullable', 'string'],
        ]);

        $permissions = collect($data['module_permissions'] ?? [])
            ->map(fn ($permission) => (string) $permission)
            ->unique()
            ->values()
            ->all();

        $user->update([
            'module_permissions' => $permissions,
        ]);

        return redirect()->route('users.index', array_filter([
            'page' => $request->input('page'),
            'department' => $request->input('department'),
            'selected_user' => $user->id,
        ]))->with('status', 'Permisos actualizados.');
    }

    public function startPreview(Request $request): RedirectResponse
    {
        $originalUser = $this->resolveOriginalUser($request);
        if (!$originalUser || $originalUser->role !== 'superadmin') {
            throw new AccessDeniedHttpException('Solo el superadmin puede usar esta vista.');
        }

        $data = $request->validate([
            'preview_user_id' => ['required', 'exists:users,id'],
        ]);

        $previewUser = User::findOrFail($data['preview_user_id']);
        if (!$previewUser->active) {
            return back()->withErrors(['preview_user_id' => 'Solo puedes ver usuarios activos.']);
        }

        if ((int) $previewUser->id === (int) $originalUser->id) {
            $request->session()->forget(['impersonation.preview_user_id', 'impersonation.origin_user_id']);

            return redirect()->route('public.dashboard')->with('status', 'Volviste a tu propia vista.');
        }

        $request->session()->put('impersonation.origin_user_id', $originalUser->id);
        $request->session()->put('impersonation.preview_user_id', $previewUser->id);

        return redirect()->route('public.dashboard')->with('status', 'Vista cambiada a ' . $previewUser->name . '.');
    }

    public function stopPreview(Request $request): RedirectResponse
    {
        $originalUser = $this->resolveOriginalUser($request);
        if (!$originalUser || $originalUser->role !== 'superadmin') {
            throw new AccessDeniedHttpException('Solo el superadmin puede salir de esta vista.');
        }

        $request->session()->forget(['impersonation.preview_user_id', 'impersonation.origin_user_id']);

        return redirect()->route('public.dashboard')->with('status', 'Vista original restaurada.');
    }

    private function roles(): array
    {
        return [
            'superadmin' => 'SuperAdmin',
            'admin' => 'Administrador',
            'user' => 'Usuario',
        ];
    }

    private function departments(): array
    {
        return [
            'compras' => 'Compras',
            'mantenimiento' => 'Mantenimiento',
            'recursos humanos' => 'Recursos Humanos',
            'gerencia' => 'Gerencia',
            'almacen' => 'Almacen',
            'sistemas' => 'Sistemas',
            'calidad' => 'Calidad',
        ];
    }

    private function resolveOriginalUser(Request $request): ?User
    {
        $originalUser = $request->attributes->get('original_user');

        return $originalUser instanceof User ? $originalUser : $request->user();
    }
}
