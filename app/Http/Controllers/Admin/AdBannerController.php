<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdBannerRequest;
use App\Models\AdBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdBannerController extends Controller
{
    public function index(): View
    {
        return view('admin.publicidad.index', [
            'banners' => AdBanner::orderByDesc('created_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.publicidad.form', ['banner' => new AdBanner()]);
    }

    public function store(AdBannerRequest $request): RedirectResponse
    {
        $banner = new AdBanner();
        $this->fillFromRequest($banner, $request);
        $banner->save();

        return redirect()->route('admin.publicidad.index')->with('status', 'Banner creado correctamente.');
    }

    public function edit(AdBanner $publicidad): View
    {
        return view('admin.publicidad.form', ['banner' => $publicidad]);
    }

    public function update(AdBannerRequest $request, AdBanner $publicidad): RedirectResponse
    {
        $this->fillFromRequest($publicidad, $request);
        $publicidad->save();

        return redirect()->route('admin.publicidad.index')->with('status', 'Banner actualizado correctamente.');
    }

    public function destroy(AdBanner $publicidad): RedirectResponse
    {
        if ($publicidad->image_url) {
            Storage::disk('public')->delete($publicidad->image_url);
        }

        $publicidad->delete();

        return redirect()->route('admin.publicidad.index')->with('status', 'Banner eliminado.');
    }

    private function fillFromRequest(AdBanner $banner, AdBannerRequest $request): void
    {
        $banner->fill([
            'position' => $request->position,
            'link_url' => $request->link_url,
            'alt_text' => $request->alt_text,
            'advertiser_name' => $request->advertiser_name,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'price_per_month' => $request->price_per_month,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('image')) {
            if ($banner->image_url) {
                Storage::disk('public')->delete($banner->image_url);
            }

            $banner->image_url = $request->file('image')->store('banners', 'public');
        }
    }
}
