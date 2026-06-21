<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const KEYS = [
        'adsense_client_id',
        'adsense_slot_header_banner',
        'adsense_slot_sidebar',
        'adsense_slot_homepage_hero',
        'adsense_slot_footer',
        'social_facebook_url',
        'social_twitter_url',
        'social_instagram_url',
        'social_youtube_url',
        'seo_meta_description_default',
        'seo_ga4_id',
        'seo_gsc_verification',
    ];

    public function show(): View
    {
        return view('admin.configuracion.show', [
            'settings' => Setting::getMany(self::KEYS),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'adsense_client_id' => ['nullable', 'string', 'max:255'],
            'adsense_slot_header_banner' => ['nullable', 'string', 'max:255'],
            'adsense_slot_sidebar' => ['nullable', 'string', 'max:255'],
            'adsense_slot_homepage_hero' => ['nullable', 'string', 'max:255'],
            'adsense_slot_footer' => ['nullable', 'string', 'max:255'],
            'social_facebook_url' => ['nullable', 'url', 'max:2048'],
            'social_twitter_url' => ['nullable', 'url', 'max:2048'],
            'social_instagram_url' => ['nullable', 'url', 'max:2048'],
            'social_youtube_url' => ['nullable', 'url', 'max:2048'],
            'seo_meta_description_default' => ['nullable', 'string', 'max:160'],
            'seo_ga4_id' => ['nullable', 'string', 'max:255'],
            'seo_gsc_verification' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.configuracion.show')->with('status', 'Configuración guardada correctamente.');
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.configuracion.show')->with('status', 'Usuario administrador creado correctamente.');
    }
}
