@extends('layouts.app')

@section('title', 'Recuperar contraseña | ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <h1 class="mb-4 text-center">Recuperar contraseña</h1>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" novalidate>
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

                        <button type="submit" class="btn btn-primary w-100">Enviar enlace de recuperación</button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        <a href="{{ route('login') }}">&larr; Volver a iniciar sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
