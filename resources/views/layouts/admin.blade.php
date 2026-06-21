<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Panel de administración | ' . config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <a class="visually-hidden-focusable" href="#contenido-principal">Saltar al contenido principal</a>

    <header class="navbar navbar-dark px-3" style="background-color: #1A1A2E;">
        <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">{{ config('app.name') }} · Admin</a>
        <div class="d-flex align-items-center gap-3">
            <a class="link-light small" href="{{ url('/') }}">Ver sitio</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
            </form>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-12 col-lg-3 col-xl-2 bg-light border-end py-4" aria-label="Navegación del panel de administración">
                <ul class="nav nav-pills flex-column gap-1">
                    @php
                        $links = [
                            'admin.dashboard' => ['Dashboard', route('admin.dashboard')],
                            'admin.loterias.index' => ['Loterías', route('admin.loterias.index')],
                            'admin.resultados.index' => ['Resultados', route('admin.resultados.index')],
                            'admin.publicidad.index' => ['Publicidad', route('admin.publicidad.index')],
                            'admin.configuracion.show' => ['Configuración', route('admin.configuracion.show')],
                        ];
                    @endphp
                    @foreach ($links as $routeName => [$label, $href])
                        @php $section = explode('.', $routeName)[1]; @endphp
                        <li class="nav-item">
                            <a
                                class="nav-link {{ request()->routeIs("admin.{$section}*") ? 'active' : 'text-dark' }}"
                                href="{{ $href }}"
                                @if (request()->routeIs("admin.{$section}*")) aria-current="page" @endif
                            >
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <main id="contenido-principal" class="col-12 col-lg-9 col-xl-10 py-4">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
