@extends('layouts.app')

@section('title', 'Cómo funciona ' . config('app.name'))
@section('description', 'Descubre cómo obtenemos, verificamos y publicamos los resultados de loterías, y cómo usar las estadísticas y el generador de números.')
@section('canonical', url('/como-funciona'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cómo funciona</li>
@endsection

@section('content')
    <h1 class="h2 mb-4">Cómo funciona {{ config('app.name') }}</h1>

    <section class="mb-4">
        <h2 class="h4">1. Obtención de resultados</h2>
        <p>Consultamos automáticamente las páginas oficiales de cada lotería después de cada sorteo y guardamos el número ganador, la fecha y, cuando aplica, la serie.</p>
    </section>

    <section class="mb-4">
        <h2 class="h4">2. Estadísticas</h2>
        <p>Con el historial de resultados calculamos cuántas veces ha salido cada número, cuándo fue la última vez y qué tan frecuente es comparado con el resto. Puedes consultarlas en la sección <a href="{{ route('estadisticas.index') }}">Estadísticas</a>.</p>
    </section>

    <section class="mb-4">
        <h2 class="h4">3. Generador de números</h2>
        <p>El <a href="{{ route('generador.show') }}">generador</a> propone combinaciones al azar o ponderadas por la frecuencia histórica de cada número. Es una herramienta de entretenimiento, no garantiza ningún resultado.</p>
    </section>
@endsection
