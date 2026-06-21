@extends('layouts.app')

@section('title', 'Restablecer contraseña | ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <h1 class="mb-4 text-center">Restablecer contraseña</h1>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('password.update') }}" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email', $email) }}"
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
                            <label for="password" class="form-label">Nueva contraseña</label>
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
                            <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
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

                        <button type="submit" class="btn btn-primary w-100">Restablecer contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
