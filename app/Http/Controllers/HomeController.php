<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $lotteries = Cache::remember('home.lotteries-with-latest-result', 600, function () {
            return Lottery::query()
                ->where('is_active', true)
                ->orderBy('display_order')
                ->with(['results' => fn ($query) => $query->latest('draw_date')->limit(1)])
                ->get();
        });

        $ultimosResultados = $lotteries
            ->filter(fn (Lottery $lottery) => $lottery->results->isNotEmpty())
            ->map(fn (Lottery $lottery) => [
                'lottery' => $lottery,
                'result' => $lottery->results->first(),
            ])
            ->values();

        $proximosSorteos = $lotteries
            ->map(fn (Lottery $lottery) => [
                'lottery' => $lottery,
                'next_draw_at' => $lottery->nextDrawAt(),
            ])
            ->filter(fn (array $item) => $item['next_draw_at'] !== null)
            ->sortBy('next_draw_at')
            ->take(3)
            ->values();

        return view('home', [
            'ultimosResultados' => $ultimosResultados,
            'proximosSorteos' => $proximosSorteos,
            'lotteries' => $lotteries,
        ]);
    }
}
