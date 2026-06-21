<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = trim($__env->yieldContent('title')) ?: config('app.name');
        $pageDescription = trim($__env->yieldContent('description')) ?: \App\Models\Setting::get('seo_meta_description_default', 'Resultados de todas las loterías de Colombia y el mundo, estadísticas y generador de números.');
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="@yield('og-image', asset('images/og-default.png'))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    @if ($gsc = \App\Models\Setting::get('seo_gsc_verification'))
        <meta name="google-site-verification" content="{{ $gsc }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @if ($adsenseClientId = \App\Models\Setting::get('adsense_client_id'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClientId }}" crossorigin="anonymous"></script>
    @endif

    @if ($ga4 = \App\Models\Setting::get('seo_ga4_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $ga4 }}');
        </script>
    @endif

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ]) !!}
    </script>

    @stack('json-ld')
</head>
<body>
    <a class="visually-hidden-focusable" href="#contenido-principal">Saltar al contenido principal</a>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1A1A2E;">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">{{ config('app.name') }}</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrincipal" aria-controls="navbarPrincipal" aria-expanded="false" aria-label="Abrir menú de navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarPrincipal">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/resultados') }}">Resultados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/estadisticas') }}">Estadísticas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/generador') }}">Generador</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/buscar') }}">Buscar número</a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/perfil') }}">{{ auth()->user()->name }}</a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
                                </form>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="btn btn-warning text-dark btn-sm" href="{{ route('login') }}">Iniciar sesión</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <x-ad-banner position="header_banner" />
    </div>

    @hasSection('breadcrumbs')
        <div class="container">
            <nav aria-label="Ruta de navegación">
                <ol class="breadcrumb">
                    @yield('breadcrumbs')
                </ol>
            </nav>
        </div>
    @endif

    <main id="contenido-principal" class="container py-4">
        @if (session('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>

    <div class="container">
        <x-ad-banner position="footer" />
    </div>

    <footer class="text-white-50 py-4 mt-5" style="background-color: #1A1A2E;">
        <div class="container">
            <nav aria-label="Enlaces de pie de página" class="mb-3">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a class="link-light" href="{{ url('/acerca-de') }}">Acerca de</a></li>
                    <li class="list-inline-item"><a class="link-light" href="{{ url('/como-funciona') }}">Cómo funciona</a></li>
                    @if ($fb = \App\Models\Setting::get('social_facebook_url'))
                        <li class="list-inline-item"><a class="link-light" href="{{ $fb }}" rel="noopener" target="_blank">Facebook</a></li>
                    @endif
                    @if ($tw = \App\Models\Setting::get('social_twitter_url'))
                        <li class="list-inline-item"><a class="link-light" href="{{ $tw }}" rel="noopener" target="_blank">X (Twitter)</a></li>
                    @endif
                    @if ($ig = \App\Models\Setting::get('social_instagram_url'))
                        <li class="list-inline-item"><a class="link-light" href="{{ $ig }}" rel="noopener" target="_blank">Instagram</a></li>
                    @endif
                </ul>
            </nav>
            <p class="mb-0 text-center">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
