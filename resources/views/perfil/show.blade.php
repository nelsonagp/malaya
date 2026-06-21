@extends('layouts.app')

@section('title', 'Mi perfil | ' . config('app.name'))

@section('content')
    <h1 class="mb-4">Mi perfil</h1>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <section aria-labelledby="datos-personales">
                <h2 id="datos-personales" class="h4">Datos personales</h2>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">{{ $user->name }}</dd>

                            <dt class="col-sm-4">Correo electrónico</dt>
                            <dd class="col-sm-8">{{ $user->email }}</dd>

                            <dt class="col-sm-4">Cuenta desde</dt>
                            <dd class="col-sm-8">{{ $user->created_at->translatedFormat('d \d\e F \d\e Y') }}</dd>
                        </dl>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section aria-labelledby="cuentas-vinculadas">
                <h2 id="cuentas-vinculadas" class="h4">Cuentas vinculadas</h2>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p>Conecta tus redes sociales para iniciar sesión más rápido.</p>
                        <a href="{{ route('perfil.cuentas') }}" class="btn btn-outline-primary">Gestionar cuentas vinculadas</a>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12">
            <section aria-labelledby="alertas-configuradas">
                <h2 id="alertas-configuradas" class="h4">Alertas configuradas</h2>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="mb-0">Próximamente podrás configurar alertas de números y loterías favoritas desde aquí.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
