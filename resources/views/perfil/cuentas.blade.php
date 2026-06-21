@extends('layouts.app')

@section('title', 'Cuentas vinculadas | ' . config('app.name'))

@section('content')
    <h1 class="mb-4">Cuentas vinculadas</h1>

    <ul class="list-group" role="list">
        @foreach ($providers as $key => $label)
            @php $account = $user->socialAccounts->firstWhere('provider', $key); @endphp
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $label }}</span>

                @if ($account)
                    <form method="POST" action="{{ route('perfil.cuentas.desvincular', $key) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Desconectar</button>
                    </form>
                @else
                    <a href="{{ route('auth.social.redirect', $key) }}"
                       class="btn btn-outline-secondary btn-sm disabled"
                       aria-disabled="true"
                       title="Disponible cuando se configuren las credenciales de {{ $label }}">
                        Conectar
                    </a>
                @endif
            </li>
        @endforeach
    </ul>

    <a href="{{ route('perfil') }}" class="btn btn-link mt-3 ps-0">&larr; Volver a mi perfil</a>
@endsection
