@extends('layouts.app')

@section('title', 'Acerca de ' . config('app.name'))
@section('description', config('app.name') . ' publica los resultados oficiales de las loterías de Colombia y del mundo, con estadísticas históricas y herramientas gratuitas para los jugadores.')
@section('canonical', url('/acerca-de'))

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active" aria-current="page">Acerca de</li>
@endsection

@section('content')
    <h1 class="h2 mb-4">Acerca de {{ config('app.name') }}</h1>
    <p>{{ config('app.name') }} es un sitio independiente que recopila y publica los resultados oficiales de las loterías colombianas (Bogotá, Medellín, Baloto, Cruz Roja y más) e internacionales (Powerball, Mega Millions, EuroMillones), apenas se conocen tras cada sorteo.</p>
    <p>No vendemos boletos ni operamos sorteos: somos una fuente de consulta gratuita. Cada resultado se obtiene directamente de las páginas oficiales de cada lotería y se verifica antes de publicarse.</p>
    <p>Además de los resultados, ofrecemos estadísticas históricas de frecuencia por número y un generador de combinaciones para quienes quieran jugar de forma informada o solo por diversión.</p>
@endsection
