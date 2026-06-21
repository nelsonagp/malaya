@php
    $clientId = \App\Models\Setting::get('adsense_client_id');
    $adsenseSlot ??= null;
@endphp
<div class="ad-banner ad-banner--{{ $slot }} my-3 text-center" role="complementary" aria-label="Publicidad">
    @if ($clientId && $adsenseSlot)
        <ins
            class="adsbygoogle"
            style="display:block"
            data-ad-client="{{ $clientId }}"
            data-ad-slot="{{ $adsenseSlot }}"
            data-ad-format="auto"
            data-full-width-responsive="true"
        ></ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    @else
        <div class="bg-secondary-subtle text-secondary-emphasis border rounded py-3 px-2 small">
            Espacio publicitario disponible — contacto@malayaseamisuerte.com
        </div>
    @endif
</div>
