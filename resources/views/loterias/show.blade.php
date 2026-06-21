@extends('layouts.app')

@section('title', $ultimoResultado
    ? "Resultado {$lottery->name} hoy " . $ultimoResultado->draw_date->translatedFormat('d \\d\\e F \\d\\e Y') . ' | ' . config('app.name')
    : "Resultados de {$lottery->name} | " . config('app.name'))

@section('description', $ultimoResultado
    ? "Resultado de la {$lottery->name} del " . $ultimoResultado->draw_date->translatedFormat('d \\d\\e F') . ': número ' . ($ultimoResultado->numbers[0] ?? '') . '. Consulta el historial completo y estadísticas.'
    : "Consulta los resultados, historial y estadísticas de la {$lottery->name}.")

@section('canonical', route('loteria.show', $lottery->slug))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('resultados.index') }}">Resultados</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $lottery->name }}</li>
@endsection

@push('json-ld')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Resultados', 'item' => route('resultados.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $lottery->name, 'item' => route('loteria.show', $lottery->slug)],
            ],
        ]) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => "¿Cuándo juega la {$lottery->name}?",
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $lottery->drawScheduleLabel() ? "La {$lottery->name} juega los {$lottery->drawScheduleLabel()}, hora de {$lottery->country}." : "Consulta el calendario oficial de la {$lottery->name}."],
                ],
                [
                    '@type' => 'Question',
                    'name' => "¿Cuál fue el último resultado de la {$lottery->name}?",
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $ultimoResultado ? "El último resultado de la {$lottery->name} fue " . ($ultimoResultado->numbers[0] ?? '') . ' el ' . $ultimoResultado->draw_date->translatedFormat('d \\d\\e F \\d\\e Y') . '.' : 'Aún no hay resultados cargados.'],
                ],
                [
                    '@type' => 'Question',
                    'name' => "¿Cuánto paga el premio mayor de la {$lottery->name}?",
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $lottery->prize_info ?: 'El valor del premio mayor varía según el sorteo; consulta los términos oficiales.'],
                ],
            ],
        ]) !!}
    </script>
@endpush

@section('content')
    <header class="mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if ($lottery->logo_url)
                <img src="{{ $lottery->logo_url }}" alt="Logo {{ $lottery->name }}" width="64" height="64" class="rounded">
            @endif
            <div>
                <h1 class="h2 mb-1">{{ $lottery->name }}</h1>
                <p class="mb-0">
                    <span class="badge bg-warning text-dark">{{ $lottery->country }}</span>
                    <span class="text-muted">Sorteo {{ $lottery->drawFrequencyLabel() }}{{ $lottery->drawScheduleLabel() ? ' · ' . $lottery->drawScheduleLabel() : '' }}</span>
                </p>
            </div>
        </div>
    </header>

    <section class="mb-4" aria-labelledby="ultimo-resultado-titulo">
        <h2 id="ultimo-resultado-titulo" class="h4 mb-3">Último resultado</h2>
        <article class="card shadow-sm">
            <div class="card-body text-center py-4">
                @if ($ultimoResultado)
                    <p class="text-muted mb-2">{{ $ultimoResultado->draw_date->translatedFormat('l d \\d\\e F \\d\\e Y') }}</p>
                    <x-numero-resultado :lottery="$lottery" :result="$ultimoResultado" size="display-4" />
                @else
                    <p class="text-muted mb-0">Aún no hay resultados cargados para esta lotería.</p>
                @endif
            </div>
        </article>

        @if ($lottery->affiliate_url)
            <p class="mt-3">
                <a class="btn btn-success" href="{{ route('loteria.afiliado', $lottery->slug) }}" target="_blank" rel="sponsored noopener">
                    🎟️ Compra tu billete en línea
                </a>
            </p>
        @endif
    </section>

    <section class="mb-4" aria-labelledby="ultimos-20-titulo">
        <h2 id="ultimos-20-titulo" class="h4 mb-3">Últimos 20 resultados</h2>
        @if ($ultimosResultados->isEmpty())
            <p class="text-muted">No hay resultados históricos todavía.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <caption class="visually-hidden">Últimos 20 resultados de {{ $lottery->name }}</caption>
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Resultado</th>
                            @if ($lottery->has_series)
                                <th scope="col">Serie</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ultimosResultados as $resultado)
                            <tr>
                                <td>{{ $resultado->draw_date->translatedFormat('d/m/Y') }}</td>
                                <td class="font-monospace fs-5">{{ $resultado->numbers[0] ?? '' }}</td>
                                @if ($lottery->has_series)
                                    <td class="font-monospace">{{ $resultado->numbers[1] ?? '' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="mb-4" aria-labelledby="stats-rapidas-titulo">
        <h2 id="stats-rapidas-titulo" class="h4 mb-3">Estadísticas rápidas</h2>
        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <h3 class="h6">Top 5 más frecuentes</h3>
                <ol class="list-group list-group-numbered">
                    @forelse ($masFrecuentes as $stat)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-monospace">{{ $stat->number }}</span>
                            <span class="badge bg-success">{{ $stat->total_appearances }} veces <span class="visually-hidden">(número caliente)</span></span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin datos suficientes.</li>
                    @endforelse
                </ol>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <h3 class="h6">Top 5 menos frecuentes</h3>
                <ol class="list-group list-group-numbered">
                    @forelse ($menosFrecuentes as $stat)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-monospace">{{ $stat->number }}</span>
                            <span class="badge bg-primary">{{ $stat->total_appearances }} veces <span class="visually-hidden">(número frío)</span></span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin datos suficientes.</li>
                    @endforelse
                </ol>
            </div>
        </div>
        <a class="btn btn-outline-success" href="{{ route('estadisticas.show', $lottery->slug) }}">Ver estadísticas completas</a>
    </section>

    <section aria-labelledby="faq-titulo">
        <h2 id="faq-titulo" class="h4 mb-3">Preguntas frecuentes</h2>
        <div class="accordion" id="faqAcordeon">
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">¿Cuándo juega la {{ $lottery->name }}?</button>
                </h3>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAcordeon">
                    <div class="accordion-body">
                        {{ $lottery->drawScheduleLabel() ? "Juega los {$lottery->drawScheduleLabel()}, hora de {$lottery->country}." : 'Consulta el calendario oficial.' }}
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">¿Cuánto paga el premio mayor?</button>
                </h3>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAcordeon">
                    <div class="accordion-body">
                        {{ $lottery->prize_info ?: 'El valor del premio mayor varía según el sorteo; consulta los términos oficiales.' }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
