@extends('layouts.admin')

@section('title', ($lottery->exists ? 'Editar' : 'Crear') . ' lotería | ' . config('app.name'))

@php
    $days = [
        'monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles',
        'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo',
    ];
    $selectedDays = old('draw_days', $lottery->draw_schedule['days'] ?? []);
@endphp

@section('content')
    <h1 class="h3 mb-4">{{ $lottery->exists ? 'Editar lotería' : 'Crear nueva lotería' }}</h1>

    <form method="POST" action="{{ $lottery->exists ? route('admin.loterias.update', $lottery) : route('admin.loterias.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($lottery->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $lottery->name) }}" required aria-required="true">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $lottery->slug) }}" placeholder="Déjalo vacío para generarlo desde el nombre">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <div class="col-8 mb-3">
                        <label for="country" class="form-label">País</label>
                        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $lottery->country ?? 'Colombia') }}" required aria-required="true">
                        @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-4 mb-3">
                        <label for="country_code" class="form-label">Código</label>
                        <input type="text" class="form-control @error('country_code') is-invalid @enderror" id="country_code" name="country_code" maxlength="2" value="{{ old('country_code', $lottery->country_code ?? 'CO') }}" required aria-required="true">
                        @error('country_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    @if ($lottery->logo_url)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $lottery->logo_url) }}" alt="Logo actual de {{ $lottery->name }}" style="height: 48px;">
                        </div>
                    @endif
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="website_url" class="form-label">URL del sitio oficial</label>
                    <input type="url" class="form-control @error('website_url') is-invalid @enderror" id="website_url" name="website_url" value="{{ old('website_url', $lottery->website_url) }}">
                    @error('website_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="results_url" class="form-label">URL de resultados (para scraping)</label>
                    <input type="url" class="form-control @error('results_url') is-invalid @enderror" id="results_url" name="results_url" value="{{ old('results_url', $lottery->results_url) }}">
                    @error('results_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="affiliate_url" class="form-label">URL de afiliado (opcional)</label>
                    <input type="url" class="form-control @error('affiliate_url') is-invalid @enderror" id="affiliate_url" name="affiliate_url" value="{{ old('affiliate_url', $lottery->affiliate_url) }}">
                    @error('affiliate_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="mb-3">
                    <label for="scraper_class" class="form-label">Clase de scraper</label>
                    <select class="form-select" id="scraper_class" name="scraper_class">
                        <option value="">Ninguno (sin scraper automático todavía)</option>
                        @foreach ($scrapers as $scraperKey)
                            <option value="{{ $scraperKey }}" @selected(old('scraper_class', $lottery->scraper_class) === $scraperKey)>{{ $scraperKey }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">El módulo de scraping aún no está construido — esta lista se llenará en config/scrapers.php.</div>
                </div>

                <div class="mb-3">
                    <label for="scraper_config" class="form-label">Configuración del scraper (JSON)</label>
                    <textarea class="form-control @error('scraper_config') is-invalid @enderror" id="scraper_config" name="scraper_config" rows="3" aria-describedby="scraper_config-help">{{ old('scraper_config', $lottery->scraper_config ? json_encode($lottery->scraper_config, JSON_PRETTY_PRINT) : '') }}</textarea>
                    <div id="scraper_config-help" class="form-text">Selectores CSS u otras opciones específicas del scraper, en formato JSON.</div>
                    @error('scraper_config') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <fieldset class="mb-3">
                    <legend class="form-label">Días de sorteo</legend>
                    <div class="row">
                        @foreach ($days as $value => $label)
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="draw_days[]" id="day-{{ $value }}" value="{{ $value }}" @checked(in_array($value, $selectedDays))>
                                    <label class="form-check-label" for="day-{{ $value }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="draw_time" class="form-label">Hora del sorteo</label>
                        <input type="time" class="form-control" id="draw_time" name="draw_time" value="{{ old('draw_time', $lottery->draw_schedule['time'] ?? '') }}">
                    </div>
                    <div class="col-6 mb-3">
                        <label for="draw_timezone" class="form-label">Zona horaria</label>
                        <input type="text" class="form-control" id="draw_timezone" name="draw_timezone" value="{{ old('draw_timezone', $lottery->draw_schedule['timezone'] ?? 'America/Bogota') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="draw_frequency" class="form-label">Frecuencia</label>
                    <select class="form-select" id="draw_frequency" name="draw_frequency">
                        @foreach (['daily' => 'Diaria', 'weekly' => 'Semanal', 'biweekly' => 'Quincenal', 'monthly' => 'Mensual'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('draw_frequency', $lottery->draw_frequency) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-4 mb-3">
                        <label for="number_count" class="form-label">N.º de cifras</label>
                        <input type="number" class="form-control @error('number_count') is-invalid @enderror" id="number_count" name="number_count" min="1" value="{{ old('number_count', $lottery->number_count ?? 4) }}" required aria-required="true">
                        @error('number_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-4 mb-3">
                        <label for="number_range_min" class="form-label">Rango mín.</label>
                        <input type="number" class="form-control @error('number_range_min') is-invalid @enderror" id="number_range_min" name="number_range_min" value="{{ old('number_range_min', $lottery->number_range_min ?? 0) }}" required aria-required="true">
                        @error('number_range_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-4 mb-3">
                        <label for="number_range_max" class="form-label">Rango máx.</label>
                        <input type="number" class="form-control @error('number_range_max') is-invalid @enderror" id="number_range_max" name="number_range_max" value="{{ old('number_range_max', $lottery->number_range_max ?? 9999) }}" required aria-required="true">
                        @error('number_range_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="has_series" name="has_series" value="1" @checked(old('has_series', $lottery->has_series))>
                    <label class="form-check-label" for="has_series">¿Tiene series?</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="has_fractions" name="has_fractions" value="1" @checked(old('has_fractions', $lottery->has_fractions))>
                    <label class="form-check-label" for="has_fractions">¿Tiene fracciones?</label>
                </div>

                <div class="mb-3">
                    <label for="prize_info" class="form-label">Información de premios</label>
                    <textarea class="form-control" id="prize_info" name="prize_info" rows="2">{{ old('prize_info', $lottery->prize_info) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="display_order" class="form-label">Orden de visualización</label>
                        <input type="number" class="form-control" id="display_order" name="display_order" value="{{ old('display_order', $lottery->display_order ?? 0) }}">
                    </div>
                    <div class="col-6 mb-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $lottery->exists ? $lottery->is_active : true))>
                            <label class="form-check-label" for="is_active">Lotería activa</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ $lottery->exists ? 'Guardar cambios' : 'Crear lotería' }}</button>
        <a href="{{ route('admin.loterias.index') }}" class="btn btn-link">Cancelar</a>
    </form>

    @if ($lottery->exists)
        <div class="alert alert-light border d-flex gap-2 align-items-center mt-4">
            @if ($lottery->scraper_class)
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnProbarScraper" data-url="{{ route('admin.loterias.test', $lottery) }}">
                    Probar scraper
                </button>
                <form method="POST" action="{{ route('admin.loterias.force-scrape', $lottery) }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Forzar scraping ahora</button>
                </form>
                <span class="small text-muted">"Forzar scraping ahora" encola el job — necesita un worker corriendo (<code>php artisan queue:work</code>) o <code>QUEUE_CONNECTION=sync</code> para ejecutarse de inmediato.</span>
            @else
                <span class="btn btn-outline-secondary btn-sm disabled" aria-disabled="true">Probar scraper</span>
                <span class="btn btn-outline-secondary btn-sm disabled" aria-disabled="true">Forzar scraping ahora</span>
                <span class="small text-muted">Asigna una clase de scraper arriba para activar estos botones.</span>
            @endif
        </div>

        <div class="modal fade" id="probarScraperModal" tabindex="-1" aria-labelledby="probarScraperModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="probarScraperModalLabel">Resultado de la prueba</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div id="probarScraperResultado" aria-live="polite">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('btnProbarScraper')?.addEventListener('click', function () {
                const modalEl = document.getElementById('probarScraperModal');
                const modal = new window.bootstrap.Modal(modalEl);
                const resultado = document.getElementById('probarScraperResultado');
                resultado.textContent = 'Cargando...';
                modal.show();

                fetch(this.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        resultado.innerHTML = data.error
                            ? '<p class="text-danger mb-0">' + data.error + '</p>'
                            : '<pre class="mb-0">' + JSON.stringify(data.results, null, 2) + '</pre>';
                    })
                    .catch(() => {
                        resultado.innerHTML = '<p class="text-danger mb-0">Error de red al probar el scraper.</p>';
                    });
            });
        </script>
    @endif
@endsection
