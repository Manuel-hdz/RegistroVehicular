<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Muestra el formulario de inicio de sesion.
    public function showLogin()
    {
        return view('auth.login');
    }

    // Muestra el formulario para crear una cuenta.
    public function showRegister()
    {
        if (Auth::check() && ! Auth::user()->isSuperadministrador()) {
            abort(403, 'Solo el superusuario puede crear usuarios desde una sesion activa.');
        }

        return view('auth.register');
    }

    // Valida credenciales y crea la sesion del usuario.
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Las credenciales no coinciden.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()));
    }

    // Registra un usuario nuevo y lo deja con sesion iniciada.
    public function register(Request $request)
    {
        if (Auth::check() && ! Auth::user()->isSuperadministrador()) {
            abort(403, 'Solo el superusuario puede crear usuarios desde una sesion activa.');
        }

        $creatingFromSuperUser = Auth::user()?->isSuperadministrador() ?? false;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', Rule::in(User::roles())],
        ]);

        if ($creatingFromSuperUser) {
            $data['role'] = $data['role'] ?? User::ROLE_USUARIO;
        } elseif (User::query()->doesntExist()) {
            $data['role'] = User::ROLE_SUPERADMINISTRADOR;
        } else {
            $data['role'] = User::ROLE_USUARIO;
        }

        $user = User::create($data);

        if ($creatingFromSuperUser) {
            return redirect()
                ->route('register')
                ->with('success', 'Usuario creado correctamente.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->homeFor($user));
    }

    // Cierra la sesion y protege el token actual.
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function homeFor(User $user): string
    {
        return $user->isAdministrador()
            ? route('empleados.index')
            : route('detector-qr');
    }
}
