<?php

namespace App\Http\Controllers;

use App\Models\LotteryResult;
use App\Models\NumberStatistic;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $numero = trim((string) $request->query('numero', ''));
        $apariciones = collect();
        $estadisticasPorLoteria = collect();

        if ($numero !== '') {
            $apariciones = LotteryResult::query()
                ->whereJsonContains('numbers', $numero)
                ->whereHas('lottery', fn ($q) => $q->where('is_active', true))
                ->with('lottery')
                ->orderByDesc('draw_date')
                ->limit(100)
                ->get();

            $estadisticasPorLoteria = NumberStatistic::query()
                ->where('number', $numero)
                ->whereHas('lottery', fn ($q) => $q->where('is_active', true))
                ->with('lottery')
                ->orderByDesc('total_appearances')
                ->get();
        }

        return view('buscar.index', [
            'numero' => $numero,
            'apariciones' => $apariciones,
            'estadisticasPorLoteria' => $estadisticasPorLoteria,
        ]);
    }
}
