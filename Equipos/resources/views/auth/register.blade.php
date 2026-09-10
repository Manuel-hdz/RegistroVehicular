@extends('layouts.app')

@section('title', 'Registrarse')

@section('content')
    @php
        $puedeElegirRol = auth()->check() && auth()->user()->isSuperadministrador();
    @endphp

    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <h1 class="h5 mb-3">Registrarse</h1>

                    @guest
                        @if(\App\Models\User::query()->exists())
                            <div class="alert alert-info">
                                Para crear usuarios con rol, primero inicia sesion como superadministrador.
                            </div>
                        @endif
                    @endguest

                    {{-- Muestra errores al crear la cuenta. --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('register.store') }}" method="POST">
                        @csrf

                        {{-- Nombre visible del usuario nuevo. --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" autofocus>
                        </div>

                        {{-- Correo que se usara para iniciar sesion. --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>

                        @if($puedeElegirRol)
                            <div class="mb-3">
                                <label for="role" class="form-label">Rol</label>
                                <select class="form-select" id="role" name="role">
                                    @foreach(\App\Models\User::roles() as $role)
                                        <option value="{{ $role }}" @selected(old('role', \App\Models\User::ROLE_USUARIO) === $role)>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Contrasena nueva; Laravel exige minimo 8 caracteres. --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Contrasena</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        {{-- Debe coincidir con el campo de contrasena. --}}
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contrasena</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Crear cuenta</button>
                    </form>

                    <p class="small text-muted mt-3 mb-0">
                        Ya tienes cuenta?
                        <a href="{{ route('login') }}">Inicia sesion</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
