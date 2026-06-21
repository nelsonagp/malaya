<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use App\Models\ScrapeLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LotteryController extends Controller
{
    public function show(Lottery $lottery): View
    {
        abort_unless($lottery->is_active, 404);

        $ultimoResultado = $lottery->results()->latest('draw_date')->first();
        $ultimosResultados = $lottery->results()->latest('draw_date')->limit(20)->get();
        $masFrecuentes = $lottery->numberStatistics()->orderByDesc('total_appearances')->limit(5)->get();
        $menosFrecuentes = $lottery->numberStatistics()->whereNotNull('last_appeared_date')->orderBy('total_appearances')->limit(5)->get();

        return view('loterias.show', [
            'lottery' => $lottery,
            'ultimoResultado' => $ultimoResultado,
            'ultimosResultados' => $ultimosResultados,
            'masFrecuentes' => $masFrecuentes,
            'menosFrecuentes' => $menosFrecuentes,
        ]);
    }

    public function affiliateClick(Lottery $lottery): RedirectResponse
    {
        abort_unless($lottery->affiliate_url, 404);

        ScrapeLog::query()->create([
            'lottery_id' => $lottery->id,
            'status' => 'affiliate_click',
            'started_at' => now(),
            'finished_at' => now(),
            'created_at' => now(),
        ]);

        return redirect()->away($lottery->affiliate_url);
    }
}
