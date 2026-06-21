@extends('layouts.app')

@php
    $sortUrl = function (string $column) use ($sort, $dir) {
        $newDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';

        return request()->fullUrlWithQuery(['sort' => $column, 'dir' => $newDir]);
    };
    $ariaSort = fn (string $column) => $sort === $column ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
@endphp

@section('title', "Estadísticas de la {$lottery->name}: números más y menos frecuentes | " . config('app.name'))
@section('description', "Tabla completa de frecuencia de números de la {$lottery->name}: veces que ha salido cada número, última fecha y días sin salir.")
@section('canonical', route('estadisticas.show', $lottery->slug))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('estadisticas.index') }}">Estadísticas</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $lottery->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-lg-9 order-lg-1">
            <h1 class="h2 mb-4">Estadísticas de la {{ $lottery->name }}</h1>

            <form method="GET" class="card shadow-sm mb-4" role="search" aria-label="Filtrar por fecha">
                <div class="card-body row g-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label for="desde" class="form-label">Desde</label>
                        <input type="date" id="desde" name="desde" class="form-control" value="{{ $desde }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="hasta" class="form-label">Hasta</label>
                        <input type="date" id="hasta" name="hasta" class="form-control" value="{{ $hasta }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-success">Filtrar</button>
                        <a href="{{ route('estadisticas.show', $lottery->slug) }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </div>
            </form>

            <section class="mb-4" aria-labelledby="grafico-titulo">
                <h2 id="grafico-titulo" class="h4 mb-3">Top 20 números más frecuentes</h2>
                <canvas
                    id="grafico-top20"
                    height="320"
                    role="img"
                    aria-label="Gráfico de barras del top 20 de números más frecuentes de {{ $lottery->name }}. El número más frecuente es {{ $top20->first()->number ?? 'N/D' }} con {{ $top20->first()->total_appearances ?? 0 }} apariciones. El detalle completo está en la tabla debajo del gráfico."
                ></canvas>
            </section>

            <section aria-labelledby="tabla-titulo">
                <h2 id="tabla-titulo" class="h4 mb-3">Tabla completa de frecuencia</h2>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <caption class="visually-hidden">Frecuencia de todos los números de {{ $lottery->name }}, ordenable por columna</caption>
                        <thead>
                            <tr>
                                <th scope="col" aria-sort="{{ $ariaSort('number') }}"><a href="{{ $sortUrl('number') }}">Número</a></th>
                                <th scope="col" aria-sort="{{ $ariaSort('total_appearances') }}"><a href="{{ $sortUrl('total_appearances') }}">Veces que ha salido</a></th>
                                <th scope="col" aria-sort="{{ $ariaSort('last_appeared_date') }}"><a href="{{ $sortUrl('last_appeared_date') }}">Última vez</a></th>
                                <th scope="col" aria-sort="{{ $ariaSort('days_since_last_appearance') }}"><a href="{{ $sortUrl('days_since_last_appearance') }}">Días sin salir</a></th>
                                <th scope="col" aria-sort="{{ $ariaSort('appearance_frequency') }}"><a href="{{ $sortUrl('appearance_frequency') }}">Frecuencia %</a></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stats as $stat)
                                <tr>
                                    <td class="font-monospace">{{ $stat->number }}</td>
                                    <td>{{ $stat->total_appearances }}</td>
                                    <td>{{ $stat->last_appeared_date?->translatedFormat('d/m/Y') ?? 'Nunca' }}</td>
                                    <td>
                                        {{ $stat->days_since_last_appearance ?? '—' }}
                                        @if (($stat->days_since_last_appearance ?? 0) > 60)
                                            <span class="badge bg-primary">Frío</span>
                                        @elseif (($stat->days_since_last_appearance ?? 999) < 14)
                                            <span class="badge bg-success">Caliente</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format(((float) $stat->appearance_frequency) * 100, 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">Sin datos suficientes para este rango.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="col-12 col-lg-3 order-lg-0 mb-4" aria-label="Otras loterías">
            <h2 class="h6">Otras loterías</h2>
            <ul class="list-unstyled">
                <li><a href="{{ route('estadisticas.index') }}">Ver todas</a></li>
            </ul>
        </aside>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('grafico-top20');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($top20->pluck('number')) !!},
                    datasets: [{
                        label: 'Veces que ha salido',
                        data: {!! json_encode($top20->pluck('total_appearances')) !!},
                        backgroundColor: '#007847',
                    }],
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } },
                },
            });
        });
    </script>
@endpush
