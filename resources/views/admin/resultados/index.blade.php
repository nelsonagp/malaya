@extends('layouts.admin')

@section('title', 'Gestión de resultados | ' . config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Resultados</h1>
        <a href="{{ route('admin.resultados.create') }}" class="btn btn-primary">Cargar resultado manual</a>
    </div>

    <form method="GET" class="row g-2 mb-4" aria-label="Filtros de resultados">
        <div class="col-12 col-md-4">
            <label for="lottery_id" class="form-label visually-hidden">Lotería</label>
            <select class="form-select" id="lottery_id" name="lottery_id">
                <option value="">Todas las loterías</option>
                @foreach ($lotteries as $lottery)
                    <option value="{{ $lottery->id }}" @selected(request('lottery_id') === $lottery->id)>{{ $lottery->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label for="desde" class="form-label visually-hidden">Desde</label>
            <input type="date" class="form-control" id="desde" name="desde" value="{{ request('desde') }}" placeholder="Desde">
        </div>
        <div class="col-6 col-md-3">
            <label for="hasta" class="form-label visually-hidden">Hasta</label>
            <input type="date" class="form-control" id="hasta" name="hasta" value="{{ request('hasta') }}" placeholder="Hasta">
        </div>
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <caption class="visually-hidden">Resultados de sorteos registrados</caption>
            <thead>
                <tr>
                    <th scope="col">Lotería</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Números</th>
                    <th scope="col">Verificado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($results as $result)
                    <tr>
                        <td>{{ $result->lottery->name }}</td>
                        <td>{{ $result->draw_date->format('d/m/Y') }}</td>
                        <td class="font-monospace">{{ implode(' - ', $result->numbers) }}</td>
                        <td>
                            @if ($result->is_verified)
                                <span class="badge bg-success">Verificado</span>
                            @else
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @endif
                        </td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.resultados.edit', $result) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form method="POST" action="{{ route('admin.resultados.destroy', $result) }}" onsubmit="return confirm('¿Eliminar este resultado?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No hay resultados registrados todavía.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $results->links() }}
@endsection
