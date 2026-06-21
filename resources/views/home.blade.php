@extends('layouts.app')

@section('title', 'Resultados de todas las loterías de Colombia hoy | ' . config('app.name'))
@section('description', 'Consulta los resultados de hoy de la Lotería de Bogotá, Medellín, Baloto, Powerball, Mega Millions y más loterías de Colombia y el mundo, con estadísticas y generador de números.')
@section('canonical', url('/'))

@section('content')
    <section class="text-center py-5" aria-labelledby="hero-titulo">
        <h1 id="hero-titulo" class="display-5 fw-bold">Los resultados de todas las loterías de Colombia en un solo lugar</h1>
        <p class="lead text-muted">Lotería de Bogotá, Medellín, Baloto, Powerball, Mega Millions y más, actualizados después de cada sorteo.</p>
    </section>

    <div class="container px-0">
        <x-ad-banner position="homepage_hero" />
    </div>

    @if ($proximosSorteos->isNotEmpty())
        <section class="mb-5" aria-labelledby="proximos-sorteos-titulo">
            <h2 id="proximos-sorteos-titulo" class="h3 mb-3">Próximos sorteos</h2>
            <div class="row">
                @foreach ($proximosSorteos as $item)
                    <div class="col-12 col-md-4 mb-3">
                        <article class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="h5 card-title">
                                    <a href="{{ route('loteria.show', $item['lottery']->slug) }}">{{ $item['lottery']->name }}</a>
                                </h3>
                                <p class="card-text mb-0">
                                    <span class="badge bg-warning text-dark">{{ $item['lottery']->country }}</span>
                                </p>
                                <p class="card-text text-muted mb-0">
                                    Próximo sorteo: {{ $item['next_draw_at']->translatedFormat('l j \\d\\e F, h:i A') }}
                                </p>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mb-5" aria-labelledby="ultimos-resultados-titulo">
        <h2 id="ultimos-resultados-titulo" class="h3 mb-3">Últimos resultados</h2>
        @if ($ultimosResultados->isEmpty())
            <p class="text-muted">Aún no hay resultados cargados. Vuelve pronto.</p>
        @else
            <div class="row">
                @foreach ($ultimosResultados as $item)
                    <div class="col-12 col-md-6 col-lg-4 mb-3">
                        <article class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="h5 card-title">
                                    <a href="{{ route('loteria.show', $item['lottery']->slug) }}">{{ $item['lottery']->name }}</a>
                                </h3>
                                <p class="card-text">
                                    <span class="badge bg-warning text-dark">{{ $item['lottery']->country }}</span>
                                    <span class="text-muted small">{{ $item['result']->draw_date->translatedFormat('d \\d\\e F \\d\\e Y') }}</span>
                                </p>
                                <p class="card-text">
                                    <x-numero-resultado :lottery="$item['lottery']" :result="$item['result']" size="fs-3" />
                                </p>
                                <a class="btn btn-outline-success btn-sm" href="{{ route('loteria.show', $item['lottery']->slug) }}">
                                    Ver resultados de {{ $item['lottery']->name }}
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section
        class="mb-5"
        aria-labelledby="generador-titulo"
        x-data="{
            loteria: 'baloto',
            cantidad: 4,
            min: 1,
            max: 43,
            numeros: [],
            generar() {
                const set = new Set();
                while (set.size < this.cantidad) {
                    set.add(Math.floor(Math.random() * (this.max - this.min + 1)) + this.min);
                }
                this.numeros = Array.from(set).sort((a, b) => a - b);
            }
        }"
        x-init="generar()"
    >
        <h2 id="generador-titulo" class="h3 mb-3">Números de la suerte hoy</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="card-text">Combinación generada al azar, solo por diversión:</p>
                <p aria-live="polite" class="mb-3">
                    <template x-for="numero in numeros" :key="numero">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white font-monospace fw-bold me-2" style="width: 2.75rem; height: 2.75rem;" x-text="numero"></span>
                    </template>
                </p>
                <button type="button" class="btn btn-success" @click="generar()">Generar otra combinación</button>
                <a href="{{ url('/generador') }}" class="btn btn-outline-success ms-2">Generador avanzado &rarr;</a>
            </div>
        </div>
    </section>
@endsection
