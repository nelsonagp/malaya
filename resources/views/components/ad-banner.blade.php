@props(['position'])
@php
    $banner = \App\Models\AdBanner::active()->forPosition($position)->inRandomOrder()->first();
    $adsenseSlot = match ($position) {
        'header_banner' => \App\Models\Setting::get('adsense_slot_header_banner'),
        'sidebar' => \App\Models\Setting::get('adsense_slot_sidebar'),
        'homepage_hero' => \App\Models\Setting::get('adsense_slot_homepage_hero'),
        'footer' => \App\Models\Setting::get('adsense_slot_footer'),
        default => null,
    };
@endphp
@if ($banner)
    <div
        class="ad-banner ad-banner--{{ $position }} my-3 text-center"
        role="complementary"
        aria-label="Publicidad"
        x-data="{}"
        x-init="fetch('{{ route('ad.impresion', $banner) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
    >
        <a href="{{ route('ad.clic', $banner) }}" rel="sponsored noopener" target="_blank">
            <img
                src="{{ $banner->image_url }}"
                alt="{{ $banner->alt_text ?: 'Publicidad' . ($banner->advertiser_name ? ' de ' . $banner->advertiser_name : '') }}"
                class="img-fluid"
                loading="lazy"
            >
        </a>
    </div>
@else
    @include('components.adsense', ['slot' => $position, 'adsenseSlot' => $adsenseSlot])
@endif
