@extends('layouts.app')

@section('title', 'Resultados de loterías de Colombia e internacionales | ' . config('app.name'))
@section('description', 'Historial completo de resultados de loterías colombianas e internacionales, con filtros por país, lotería y rango de fechas.')
@section('canonical', route('resultados.index'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Resultados</li>
@endsection

@section('content')
    <h1 class="h2 mb-4">Resultados de loterías</h1>

    <form method="GET" action="{{ route('resultados.index') }}" class="card shadow-sm mb-4" role="search" aria-label="Filtrar resultados">
        <div class="card-body row g-3">
            <div class="col-12 col-md-3">
                <label for="filtro-pais" class="form-label">País</label>
                <select id="filtro-pais" name="pais" class="form-select">
                    <option value="">Todos</option>
                    <option value="colombia" @selected(($filtros['pais'] ?? '') === 'colombia')>Colombia</option>
                    <option value="internacional" @selected(($filtros['pais'] ?? '') === 'internacional')>Internacional</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="filtro-loteria" class="form-label">Lotería</label>
                <select id="filtro-loteria" name="loteria" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($lotteries as $lottery)
                        <option value="{{ $lottery->slug }}" @selected(($filtros['loteria'] ?? '') === $lottery->slug)>{{ $lottery->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="filtro-desde" class="form-label">Desde</label>
                <input type="date" id="filtro-desde" name="desde" class="form-control" value="{{ $filtros['desde'] ?? '' }}">
            </div>
            <div class="col-6 col-md-3">
                <label for="filtro-hasta" class="form-label">Hasta</label>
                <input type="date" id="filtro-hasta" name="hasta" class="form-control" value="{{ $filtros['hasta'] ?? '' }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Filtrar</button>
                <a href="{{ route('resultados.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
                @auth
                    <a href="{{ route('resultados.exportar', request()->query()) }}" class="btn btn-outline-success float-md-end">Exportar a CSV</a>
                @else
                    <span class="text-muted float-md-end small">
                        <a href="{{ route('login') }}">Inicia sesión</a> para exportar a CSV
                    </span>
                @endauth
            </div>
        </div>
    </form>

    @if ($resultados->isEmpty())
        <p class="text-muted">No se encontraron resultados con esos filtros.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <caption class="visually-hidden">Resultados de loterías ordenados por fecha descendente</caption>
                <thead>
                    <tr>
                        <th scope="col">Lotería</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resultados as $resultado)
                        <tr>
                            <td><a href="{{ route('loteria.show', $resultado->lottery->slug) }}">{{ $resultado->lottery->name }}</a></td>
                            <td>{{ $resultado->draw_date->translatedFormat('d/m/Y') }}</td>
                            <td><x-numero-resultado :lottery="$resultado->lottery" :result="$resultado" size="fs-5" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <nav aria-label="Paginación de resultados">
            {{ $resultados->links() }}
        </nav>
    @endif
@endsection
