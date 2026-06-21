@extends('layouts.admin')

@section('title', 'Logs de scraping — ' . $lottery->name . ' | ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-4">Logs de scraping — {{ $lottery->name }}</h1>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <caption class="visually-hidden">Últimos 50 intentos de scraping de {{ $lottery->name }}</caption>
            <thead>
                <tr>
                    <th scope="col">Inicio</th>
                    <th scope="col">Fin</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Resultados encontrados</th>
                    <th scope="col">Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $log->finished_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $log->status ?? '—' }}</td>
                        <td>{{ $log->results_found }}</td>
                        <td>{{ $log->error_message ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Todavía no hay intentos de scraping registrados para esta lotería.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('admin.loterias.index') }}" class="btn btn-link ps-0">&larr; Volver a loterías</a>
@endsection
