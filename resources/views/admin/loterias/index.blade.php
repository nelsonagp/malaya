@extends('layouts.admin')

@section('title', 'Gestión de loterías | ' . config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h3 mb-0">Loterías</h1>
        <a href="{{ route('admin.loterias.create') }}" class="btn btn-primary">Crear nueva lotería</a>
    </div>
    <p class="text-muted mb-4">Catálogo de loterías del sitio: cuáles están activas (visibles al público), su frecuencia de sorteo y el estado de su scraping automático. Edita una lotería para asignarle un scraper, cambiar su horario de sorteo o desactivarla temporalmente.</p>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <caption class="visually-hidden">Listado de loterías registradas</caption>
            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">País</th>
                    <th scope="col">Activa</th>
                    <th scope="col">Último scraping</th>
                    <th scope="col">Frecuencia</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lotteries as $lottery)
                    <tr>
                        <td>{{ $lottery->name }}</td>
                        <td>{{ $lottery->country }}</td>
                        <td>
                            @if ($lottery->is_active)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td>{{ $lottery->last_scraped_at?->format('d/m/Y H:i') ?? 'Sin registros' }}</td>
                        <td>{{ $lottery->draw_frequency ?? '—' }}</td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.loterias.edit', $lottery) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <a href="{{ route('admin.loterias.logs', $lottery) }}" class="btn btn-sm btn-outline-secondary">Ver logs</a>
                            <form method="POST" action="{{ route('admin.loterias.destroy', $lottery) }}" onsubmit="return confirm('¿Eliminar esta lotería? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No hay loterías registradas todavía.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
