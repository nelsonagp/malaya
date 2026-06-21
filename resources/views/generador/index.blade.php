@extends('layouts.app')

@section('title', 'Generador de números de la suerte | ' . config('app.name'))
@section('description', 'Genera combinaciones de números para tu lotería favorita: al azar o basadas en las estadísticas de frecuencia histórica.')
@section('canonical', route('generador.show'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Generador</li>
@endsection

@section('content')
    <h1 class="h2 mb-4">Generador de números</h1>

    <div
        class="card shadow-sm"
        x-data="{
            loteria: '{{ $lotteries->first()?->slug }}',
            modo: 'aleatorio',
            cantidad: 1,
            cargando: false,
            error: null,
            combinaciones: [],
            async generar() {
                this.cargando = true;
                this.error = null;
                this.combinaciones = [];
                try {
                    const respuesta = await fetch(`/api/generar/${this.loteria}?modo=${this.modo}&cantidad=${this.cantidad}`);
                    if (!respuesta.ok) throw new Error('No se pudo generar la combinación.');
                    const datos = await respuesta.json();
                    this.combinaciones = datos.combinaciones;
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.cargando = false;
                }
            }
        }"
        x-init="generar()"
    >
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label for="gen-loteria" class="form-label">Lotería</label>
                    <select id="gen-loteria" class="form-select" x-model="loteria" @change="generar()">
                        @foreach ($lotteries as $lottery)
                            <option value="{{ $lottery->slug }}">{{ $lottery->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <fieldset>
                        <legend class="form-label">Modo</legend>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="modo-aleatorio" value="aleatorio" x-model="modo" @change="generar()">
                            <label class="form-check-label" for="modo-aleatorio">Aleatorio puro</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="modo-estadistico" value="estadistico" x-model="modo" @change="generar()">
                            <label class="form-check-label" for="modo-estadistico">Basado en estadísticas</label>
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 col-md-4">
                    <label for="gen-cantidad" class="form-label">Cantidad de combinaciones</label>
                    <select id="gen-cantidad" class="form-select" x-model.number="cantidad" @change="generar()">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
            </div>

            <button type="button" class="btn btn-success mb-3" @click="generar()">Generar de nuevo</button>

            <div aria-live="polite">
                <p x-show="cargando">Cargando combinaciones...</p>
                <p x-show="error" x-text="error" class="text-danger"></p>
                <ul class="list-unstyled" x-show="!cargando && combinaciones.length">
                    <template x-for="(combinacion, indice) in combinaciones" :key="indice">
                        <li class="mb-2 d-flex flex-wrap gap-2 align-items-center">
                            <span class="text-muted small">Combinación <span x-text="indice + 1"></span>:</span>
                            <template x-for="numero in combinacion" :key="numero">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white font-monospace fw-bold" style="width: 2.75rem; height: 2.75rem;" x-text="numero"></span>
                            </template>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
@endsection
