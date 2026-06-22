@extends('layouts.admin')

@section('content')
    <h1 class="mb-4">Panel de administración</h1>
    <p class="text-muted">Bienvenido, {{ auth()->user()->name }}.</p>
    <p class="text-muted mb-4">Resumen general del sitio: cuántas loterías hay activas, cuántos resultados y usuarios hay registrados, y el estado del último intento de scraping de cada lotería. No se configura nada aquí — es solo un vistazo rápido del estado del sistema.</p>

    <div class="row g-3 mb-5">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Loterías activas</p>
                    <p class="display-6 mb-0">{{ $activeLotteries }}</p>
                    <p class="text-muted small mb-0">{{ $inactiveLotteries }} inactivas</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Resultados en BD</p>
                    <p class="display-6 mb-0">{{ $totalResults }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Usuarios registrados</p>
                    <p class="display-6 mb-0">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1 small">Clics en publicidad (acumulado)</p>
                    <p class="display-6 mb-0">{{ $totalAdClicks }}</p>
                    <p class="text-muted small mb-0">El esquema actual no registra clics por día — ver nota abajo.</p>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">Último scraping por lotería</h2>
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <caption class="visually-hidden">Estado del último scraping por lotería</caption>
            <thead>
                <tr>
                    <th scope="col">Lotería</th>
                    <th scope="col">Último scraping</th>
                    <th scope="col">Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lotteries as $lottery)
                    <tr>
                        <td>{{ $lottery->name }}</td>
                        <td>{{ $lottery->last_scraped_at?->format('d/m/Y H:i') ?? 'Sin registros' }}</td>
                        <td>{{ $lottery->scrape_error ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No hay loterías registradas todavía.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
