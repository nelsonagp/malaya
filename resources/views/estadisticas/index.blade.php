@extends('layouts.app')

@section('title', 'Estadísticas de loterías de Colombia | ' . config('app.name'))
@section('description', 'Consulta las estadísticas de números más y menos frecuentes de cada lotería de Colombia e internacional.')
@section('canonical', route('estadisticas.index'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Estadísticas</li>
@endsection

@section('content')
    <h1 class="h2 mb-4">Estadísticas por lotería</h1>

    <ul class="row list-unstyled">
        @foreach ($lotteries as $lottery)
            <li class="col-12 col-md-6 col-lg-4 mb-3">
                <article class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">
                            <a href="{{ route('estadisticas.show', $lottery->slug) }}">{{ $lottery->name }}</a>
                        </h2>
                        <span class="badge bg-warning text-dark">{{ $lottery->country }}</span>
                    </div>
                </article>
            </li>
        @endforeach
    </ul>
@endsection
