@extends('layouts.app')

@section('title', 'Iniciar sesion')


@push('styles')
    <style>
        .auth-card {
            border: 0;
            border-radius: 8px;
            overflow: hidden;
        }

        .login-image-frame {
            width: 184px;
            height: 184px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background:
                linear-gradient(#ffffff, #ffffff) padding-box,
                linear-gradient(135deg, #299c44, #f0c94a) border-box;
            border: 5px solid transparent;
            box-shadow: 0 14px 28px rgba(33, 37, 41, 0.16);
        }

        .login-image {
            width: 166px;
            height: 166px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
@endpush
@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card auth-card shadow-sm">
                <div class="card-body text-center">
                    {{-- Imagen circular del login que cambia al escribir o mostrar la contrasena. --}}
                    <div class="login-image-frame mx-auto mb-3">
                        <img
                            src="{{ asset('img/login-default.jpeg') }}"
                            class="login-image"
                            alt="Imagen de inicio de sesion"
                            id="loginImage"
                            data-default-src="{{ asset('img/login-default.jpeg') }}"
                            data-password-src="{{ asset('img/login-password.jfif') }}"
                            data-visible-src="{{ asset('img/login-password-visible.jpeg') }}"
                        >
                    </div>
                    <h1 class="h5 mb-3">Iniciar sesion</h1>

                    {{-- Muestra errores de autenticacion o validacion. --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('login.store') }}" method="POST" class="text-start">
                        @csrf

                        {{-- Correo del usuario que intenta iniciar sesion. --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" autofocus>
                        </div>

                        {{-- Campo de contrasena con boton para mostrar u ocultar el texto. --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Contrasena</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">Mostrar</button>
                            </div>
                        </div>

                        {{-- Permite mantener la sesion activa por mas tiempo. --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>

                    <p class="small text-muted mt-3 mb-0">
                        Para dudas y preguntas comunicarse con el area de sistemas
                    </p>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Elementos necesarios para cambiar la imagen del login.
        const loginImage = document.getElementById('loginImage');
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        // Decide que imagen mostrar segun el estado de la contrasena.
        const updateLoginImage = () => {
            if (passwordInput.type === 'text') {
                loginImage.src = loginImage.dataset.visibleSrc;
                return;
            }

            loginImage.src = passwordInput.value
                ? loginImage.dataset.passwordSrc
                : loginImage.dataset.defaultSrc;
        };

        // Al escribir contrasena se cambia la imagen por la version cubierta.
        passwordInput.addEventListener('input', updateLoginImage);

        // Alterna entre ver y ocultar la contrasena.
        togglePassword.addEventListener('click', () => {
            const showingPassword = passwordInput.type === 'text';

            passwordInput.type = showingPassword ? 'password' : 'text';
            togglePassword.textContent = showingPassword ? 'Mostrar' : 'Ocultar';
            updateLoginImage();
        });
    </script>
@endpush
