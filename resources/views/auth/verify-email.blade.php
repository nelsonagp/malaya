@extends('layouts.app')

@section('title', 'Verifica tu correo | ' . config('app.name'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <h1 class="mb-4 text-center">Verifica tu correo</h1>

            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    <p>
                        Te enviamos un enlace de verificación a
                        <strong>{{ auth()->user()->email }}</strong>.
                        Haz clic en ese enlace para activar tu cuenta.
                    </p>
                    <p class="text-muted small">Si no lo ves, revisa tu carpeta de spam.</p>

                    <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">Reenviar enlace de verificación</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-link">Cerrar sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
