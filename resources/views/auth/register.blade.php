@extends('layouts.app')

@section('title', 'Crear cuenta | ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <h1 class="mb-4 text-center">Crear cuenta</h1>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                aria-required="true"
                                autocomplete="name"
                                @error('name') aria-describedby="name-error" @enderror
                            >
                            @error('name')
                                <div id="name-error" class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

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
                                autocomplete="new-password"
                                @error('password') aria-describedby="password-error" @enderror
                            >
                            @error('password')
                                <div id="password-error" class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                aria-required="true"
                                autocomplete="new-password"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Crear cuenta</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
