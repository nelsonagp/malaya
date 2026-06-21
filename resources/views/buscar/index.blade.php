@extends('layouts.app')

@section('title', ($numero !== '' ? "¿Dónde ha salido el número {$numero}? " : 'Buscar número en loterías') . ' | ' . config('app.name'))
@section('description', $numero !== '' ? "Consulta en qué loterías y fechas ha salido el número {$numero}, con frecuencia comparada entre loterías." : 'Busca un número y descubre en qué loterías de Colombia ha salido, cuándo y con qué frecuencia.')
@section('canonical', route('buscar.index') . ($numero !== '' ? '?numero=' . urlencode($numero) : ''))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Buscar número</li>
@endsection

@section('content')
    <h1 class="h2 mb-4">Buscar un número</h1>

    <form method="GET" action="{{ route('buscar.index') }}" class="card shadow-sm mb-4" role="search" aria-label="Buscar número en resultados de loterías">
        <div class="card-body row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label for="numero" class="form-label">Número</label>
                <input type="text" id="numero" name="numero" class="form-control" value="{{ $numero }}" inputmode="numeric" pattern="[0-9]*" required aria-required="true">
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-success">Buscar</button>
            </div>
        </div>
    </form>

    @if ($numero !== '')
        <section aria-labelledby="resultados-busqueda-titulo" class="mb-4">
            <h2 id="resultados-busqueda-titulo" class="h4 mb-3">Apariciones del número {{ $numero }}</h2>

            @if ($apariciones->isEmpty())
                <p class="text-muted">El número {{ $numero }} no ha salido todavía en ninguna lotería registrada.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <caption class="visually-hidden">Fechas y loterías donde ha salido el número {{ $numero }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">Lotería</th>
                                <th scope="col">Fecha del sorteo</th>
                                <th scope="col">Sorteo N°</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($apariciones as $resultado)
                                <tr>
                                    <td><a href="{{ route('loteria.show', $resultado->lottery->slug) }}">{{ $resultado->lottery->name }}</a></td>
                                    <td>{{ $resultado->draw_date->translatedFormat('d/m/Y') }}</td>
                                    <td>{{ $resultado->draw_number ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @if ($estadisticasPorLoteria->isNotEmpty())
            <section aria-labelledby="frecuencia-comparada-titulo">
                <h2 id="frecuencia-comparada-titulo" class="h4 mb-3">Frecuencia comparada entre loterías</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <caption class="visually-hidden">Frecuencia del número {{ $numero }} en cada lotería</caption>
                        <thead>
                            <tr>
                                <th scope="col">Lotería</th>
                                <th scope="col">Veces que ha salido</th>
                                <th scope="col">Frecuencia %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estadisticasPorLoteria as $stat)
                                <tr>
                                    <td><a href="{{ route('estadisticas.show', $stat->lottery->slug) }}">{{ $stat->lottery->name }}</a></td>
                                    <td>{{ $stat->total_appearances }}</td>
                                    <td>{{ number_format(((float) $stat->appearance_frequency) * 100, 2) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif
@endsection
