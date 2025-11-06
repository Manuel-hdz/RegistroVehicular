<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    // Solo para SuperAdmin (restringido por middleware en rutas)
    public function index(): View
    {
        $users = User::orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = ['superadmin' => 'SuperAdmin', 'admin' => 'Administrador', 'user' => 'Usuario'];
        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'username' => ['required','string','max:191','unique:users,username'],
            'password' => ['required','string','min:6'],
            'role' => ['required', Rule::in(['superadmin','admin','user'])],
            'active' => ['nullable','boolean'],
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['password'] = bcrypt($data['password']);

        User::create($data);
        return redirect()->route('users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        $roles = ['superadmin' => 'SuperAdmin', 'admin' => 'Administrador', 'user' => 'Usuario'];
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'username' => ['required','string','max:191','unique:users,username,'.$user->id],
            'password' => ['nullable','string','min:6'],
            'role' => ['required', Rule::in(['superadmin','admin','user'])],
            'active' => ['nullable','boolean'],
        ]);

        $data['active'] = $request->boolean('active', true);
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return redirect()->route('users.index')->with('status', 'Usuario actualizado.');
    }
}
