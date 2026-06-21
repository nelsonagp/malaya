@extends('layouts.admin')

@section('title', 'Configuración | ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-4">Configuración</h1>

    <form method="POST" action="{{ route('admin.configuracion.update') }}" novalidate>
        @csrf
        @method('PUT')

        <section aria-labelledby="adsense-heading" class="mb-5">
            <h2 id="adsense-heading" class="h5">Google AdSense</h2>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="adsense_client_id" class="form-label">Client ID</label>
                    <input type="text" class="form-control @error('adsense_client_id') is-invalid @enderror" id="adsense_client_id" name="adsense_client_id" value="{{ old('adsense_client_id', $settings['adsense_client_id']) }}" placeholder="ca-pub-XXXXXXXXXXXXXXXX">
                    @error('adsense_client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row g-3 mt-2">
                @foreach (['adsense_slot_header_banner' => 'Slot — Header', 'adsense_slot_sidebar' => 'Slot — Sidebar', 'adsense_slot_homepage_hero' => 'Slot — Hero de inicio', 'adsense_slot_footer' => 'Slot — Footer'] as $key => $label)
                    <div class="col-12 col-md-3">
                        <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                        <input type="text" class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}">
                    </div>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="redes-heading" class="mb-5">
            <h2 id="redes-heading" class="h5">Redes sociales del sitio</h2>
            <div class="row g-3">
                @foreach (['social_facebook_url' => 'Facebook', 'social_twitter_url' => 'Twitter / X', 'social_instagram_url' => 'Instagram', 'social_youtube_url' => 'YouTube'] as $key => $label)
                    <div class="col-12 col-md-6">
                        <label for="{{ $key }}" class="form-label">{{ $label }}</label>
                        <input type="url" class="form-control @error($key) is-invalid @enderror" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}">
                        @error($key) <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="seo-heading" class="mb-5">
            <h2 id="seo-heading" class="h5">SEO global</h2>
            <div class="row g-3">
                <div class="col-12">
                    <label for="seo_meta_description_default" class="form-label">Meta description por defecto</label>
                    <textarea class="form-control @error('seo_meta_description_default') is-invalid @enderror" id="seo_meta_description_default" name="seo_meta_description_default" rows="2" maxlength="160">{{ old('seo_meta_description_default', $settings['seo_meta_description_default']) }}</textarea>
                    @error('seo_meta_description_default') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label for="seo_ga4_id" class="form-label">Google Analytics 4 — ID de medición</label>
                    <input type="text" class="form-control" id="seo_ga4_id" name="seo_ga4_id" value="{{ old('seo_ga4_id', $settings['seo_ga4_id']) }}" placeholder="G-XXXXXXXXXX">
                </div>
                <div class="col-12 col-md-6">
                    <label for="seo_gsc_verification" class="form-label">Google Search Console — meta de verificación</label>
                    <input type="text" class="form-control" id="seo_gsc_verification" name="seo_gsc_verification" value="{{ old('seo_gsc_verification', $settings['seo_gsc_verification']) }}">
                </div>
            </div>
        </section>

        <button type="submit" class="btn btn-primary">Guardar configuración</button>
    </form>

    <hr class="my-5">

    <section aria-labelledby="crear-admin-heading">
        <h2 id="crear-admin-heading" class="h5">Crear usuario administrador</h2>
        <form method="POST" action="{{ route('admin.configuracion.usuarios.store') }}" class="row g-3" novalidate>
            @csrf
            <div class="col-12 col-md-4">
                <label for="admin_name" class="form-label">Nombre</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="admin_name" name="name" required aria-required="true">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="admin_email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="admin_email" name="email" required aria-required="true">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-2">
                <label for="admin_password" class="form-label">Contraseña</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="admin_password" name="password" required aria-required="true">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-md-2">
                <label for="admin_password_confirmation" class="form-label">Confirmar</label>
                <input type="password" class="form-control" id="admin_password_confirmation" name="password_confirmation" required aria-required="true">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-primary">Crear administrador</button>
            </div>
        </form>
    </section>
@endsection
