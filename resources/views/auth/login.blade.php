@extends('layouts.app')

@section('content')
<div class="card" style="max-width:480px; margin:40px auto;">
    <h2 style="margin-top:0">Iniciar sesión</h2>
    <form method="POST" action="{{ route('login.post') }}" class="grid">
        @csrf
        <div>
            <label>Correo</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <label style="display:flex; gap:8px; align-items:center; margin:4px 0 12px 0;">
            <input type="checkbox" name="remember" value="1"> Recordarme
        </label>
        <div class="row">
            <button class="btn btn-primary" type="submit">Entrar</button>
        </div>
    </form>
</div>
@endsection

