@extends('layouts.admin')

@section('title', 'Publicidad | ' . config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="h3 mb-0">Publicidad</h1>
        <a href="{{ route('admin.publicidad.create') }}" class="btn btn-primary">Crear banner</a>
    </div>
    <p class="text-muted mb-2">Banners de anunciantes que se muestran en el sitio público, por posición (header, sidebar, hero de inicio, footer). Si una posición no tiene un banner activo, se muestra el anuncio de AdSense de esa posición o, si tampoco hay AdSense configurado, un espacio reservado.</p>
    <p class="text-muted">El ID de cliente de Google AdSense se configura en <a href="{{ route('admin.configuracion.show') }}">Configuración</a>.</p>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <caption class="visually-hidden">Banners publicitarios configurados</caption>
            <thead>
                <tr>
                    <th scope="col">Imagen</th>
                    <th scope="col">Posición</th>
                    <th scope="col">Anunciante</th>
                    <th scope="col">Vigencia</th>
                    <th scope="col">Impresiones</th>
                    <th scope="col">Clics</th>
                    <th scope="col">Activo</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($banners as $banner)
                    <tr>
                        <td>
                            @if ($banner->image_url)
                                <img src="{{ asset('storage/' . $banner->image_url) }}" alt="{{ $banner->alt_text }}" style="height: 36px;">
                            @endif
                        </td>
                        <td>{{ $banner->position }}</td>
                        <td>{{ $banner->advertiser_name ?? '—' }}</td>
                        <td>
                            {{ $banner->starts_at?->format('d/m/Y') ?? '—' }} – {{ $banner->ends_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td>{{ $banner->impression_count }}</td>
                        <td>{{ $banner->click_count }}</td>
                        <td>
                            @if ($banner->is_active)
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('admin.publicidad.edit', $banner) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form method="POST" action="{{ route('admin.publicidad.destroy', $banner) }}" onsubmit="return confirm('¿Eliminar este banner?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No hay banners registrados todavía.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
