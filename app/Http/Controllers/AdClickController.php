<?php

namespace App\Http\Controllers;

use App\Models\AdBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AdClickController extends Controller
{
    public function impression(AdBanner $banner): JsonResponse
    {
        $banner->increment('impression_count');

        return response()->json(['status' => 'ok']);
    }

    public function click(AdBanner $banner): RedirectResponse
    {
        $banner->increment('click_count');

        return redirect()->away($banner->link_url);
    }
}
