<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Solo para SuperAdmin (restringido por middleware en rutas)
    public function index(Request $request): View
    {
        $query = User::query()->orderBy('name');
        $departments = [
            '' => 'Todos',
            'compras' => 'Compras',
            'mantenimiento' => 'Mantenimiento',
            'recursos humanos' => 'Recursos Humanos',
            'gerencia' => 'Gerencia',
            'almacen' => 'Almacén',
            'sistemas' => 'Sistemas',
            'calidad' => 'Calidad',
        ];
        if ($request->filled('department')) {
            $query->where('department', $request->string('department'));
        }
        $users = $query->paginate(20)->appends($request->query());
        return view('users.index', compact('users','departments'));
    }

    public function create(): View
    {
        $roles = ['superadmin' => 'SuperAdmin', 'admin' => 'Administrador', 'user' => 'Usuario'];
        $departments = [
            'compras' => 'Compras',
            'mantenimiento' => 'Mantenimiento',
            'recursos humanos' => 'Recursos Humanos',
            'gerencia' => 'Gerencia',
            'almacen' => 'Almacén',
            'sistemas' => 'Sistemas',
            'calidad' => 'Calidad',
        ];
        return view('users.create', compact('roles','departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'username' => ['required','string','max:191','unique:users,username'],
            'password' => ['required','string','min:6'],
            'role' => ['required', Rule::in(['superadmin','admin','user'])],
            'department' => ['required', Rule::in(['compras','mantenimiento','recursos humanos','gerencia','almacen','sistemas','calidad'])],
            'active' => ['nullable','boolean'],
        ]);

        $data['active'] = $request->has('active');
        $data['password'] = bcrypt($data['password']);

        User::create($data);
        return redirect()->route('users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        $roles = ['superadmin' => 'SuperAdmin', 'admin' => 'Administrador', 'user' => 'Usuario'];
        $departments = [
            'compras' => 'Compras',
            'mantenimiento' => 'Mantenimiento',
            'recursos humanos' => 'Recursos Humanos',
            'gerencia' => 'Gerencia',
            'almacen' => 'Almacén',
            'sistemas' => 'Sistemas',
            'calidad' => 'Calidad',
        ];
        return view('users.edit', compact('user','roles','departments'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'username' => ['required','string','max:191','unique:users,username,'.$user->id],
            'password' => ['nullable','string','min:6'],
            'role' => ['required', Rule::in(['superadmin','admin','user'])],
            'department' => ['required', Rule::in(['compras','mantenimiento','recursos humanos','gerencia','almacen','sistemas','calidad'])],
            'active' => ['nullable','boolean'],
        ]);

        $data['active'] = $request->has('active');
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $page = $request->input('page');
        return redirect()->route('users.index', array_filter(['page' => $page]))->with('status', 'Usuario actualizado.');
    }
}
