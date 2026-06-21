@extends('layouts.app')

@section('title', 'Iniciar sesión | ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <h1 class="mb-4 text-center">Iniciar sesión</h1>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                aria-required="true"
                                autocomplete="email"
                                @error('email') aria-describedby="email-error" @enderror
                            >
                            @error('email')
                                <div id="email-error" class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                required
                                aria-required="true"
                                autocomplete="current-password"
                                @error('password') aria-describedby="password-error" @enderror
                            >
                            @error('password')
                                <div id="password-error" class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Entrar</button>
                    </form>

                    <hr class="my-4">

                    <p class="text-center text-muted small mb-3">Iniciar sesión con (disponible próximamente):</p>
                    <div class="d-grid gap-2">
                        <span class="btn btn-outline-secondary disabled" aria-disabled="true">Continuar con Google</span>
                        <span class="btn btn-outline-secondary disabled" aria-disabled="true">Continuar con Facebook</span>
                    </div>

                    <p class="text-center mt-4 mb-0">
                        ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
