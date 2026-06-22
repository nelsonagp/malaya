@extends('layouts.admin')

@section('title', ($banner->exists ? 'Editar' : 'Crear') . ' banner | ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-2">{{ $banner->exists ? 'Editar banner' : 'Crear banner' }}</h1>
    <p class="text-muted mb-4">Dónde aparece el banner (posición), su imagen y texto alternativo, a dónde enlaza al hacer clic, y durante qué fechas está vigente. Fuera de ese rango de fechas, o si "Banner activo" está desmarcado, esta posición vuelve a mostrar AdSense o el espacio reservado.</p>

    <form method="POST" action="{{ $banner->exists ? route('admin.publicidad.update', $banner) : route('admin.publicidad.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($banner->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="position" class="form-label">Posición</label>
                <select class="form-select @error('position') is-invalid @enderror" id="position" name="position" required aria-required="true">
                    @foreach (['header_banner' => 'Header', 'sidebar' => 'Sidebar', 'homepage_hero' => 'Hero de inicio', 'footer' => 'Footer'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('position', $banner->position) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="advertiser_name" class="form-label">Anunciante</label>
                <input type="text" class="form-control" id="advertiser_name" name="advertiser_name" value="{{ old('advertiser_name', $banner->advertiser_name) }}">
            </div>

            <div class="col-12">
                <label for="image" class="form-label">Imagen del banner</label>
                @if ($banner->image_url)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $banner->image_url) }}" alt="{{ $banner->alt_text }}" style="height: 60px;">
                    </div>
                @endif
                <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label for="alt_text" class="form-label">Texto alternativo (accesibilidad)</label>
                <input type="text" class="form-control @error('alt_text') is-invalid @enderror" id="alt_text" name="alt_text" value="{{ old('alt_text', $banner->alt_text) }}" required aria-required="true">
                @error('alt_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label for="link_url" class="form-label">URL de destino</label>
                <input type="url" class="form-control @error('link_url') is-invalid @enderror" id="link_url" name="link_url" value="{{ old('link_url', $banner->link_url) }}" required aria-required="true">
                @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="starts_at" class="form-label">Inicia</label>
                <input type="date" class="form-control" id="starts_at" name="starts_at" value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d')) }}">
            </div>

            <div class="col-12 col-md-4">
                <label for="ends_at" class="form-label">Termina</label>
                <input type="date" class="form-control @error('ends_at') is-invalid @enderror" id="ends_at" name="ends_at" value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d')) }}">
                @error('ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="price_per_month" class="form-label">Precio mensual</label>
                <input type="number" step="0.01" class="form-control" id="price_per_month" name="price_per_month" value="{{ old('price_per_month', $banner->price_per_month) }}">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $banner->exists ? $banner->is_active : true))>
                    <label class="form-check-label" for="is_active">Banner activo</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{ $banner->exists ? 'Guardar cambios' : 'Crear banner' }}</button>
        <a href="{{ route('admin.publicidad.index') }}" class="btn btn-link">Cancelar</a>
    </form>
@endsection
