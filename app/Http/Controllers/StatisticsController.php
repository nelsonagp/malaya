<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    private const SORTABLE_COLUMNS = ['number', 'total_appearances', 'last_appeared_date', 'days_since_last_appearance', 'appearance_frequency'];

    public function index(): View
    {
        $lotteries = Lottery::query()->where('is_active', true)->orderBy('display_order')->get();

        return view('estadisticas.index', ['lotteries' => $lotteries]);
    }

    public function show(Request $request, Lottery $lottery): View
    {
        abort_unless($lottery->is_active, 404);

        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $sort = in_array($request->query('sort'), self::SORTABLE_COLUMNS, true) ? $request->query('sort') : 'total_appearances';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $stats = ($desde || $hasta)
            ? $this->computeStatsForRange($lottery, $desde, $hasta)
            : $lottery->numberStatistics()->get();

        $stats = $dir === 'asc' ? $stats->sortBy($sort)->values() : $stats->sortByDesc($sort)->values();

        $top20 = $stats->sortByDesc('total_appearances')->take(20)->values();

        return view('estadisticas.show', [
            'lottery' => $lottery,
            'stats' => $stats,
            'top20' => $top20,
            'sort' => $sort,
            'dir' => $dir,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    private function computeStatsForRange(Lottery $lottery, ?string $desde, ?string $hasta): Collection
    {
        $query = $lottery->results();

        if ($desde) {
            $query->whereDate('draw_date', '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate('draw_date', '<=', $hasta);
        }

        $resultados = $query->orderBy('draw_date')->get();
        $totalDraws = $resultados->count();
        $tally = [];

        foreach ($resultados as $resultado) {
            foreach ($resultado->numbers as $numero) {
                $tally[$numero]['total_appearances'] = ($tally[$numero]['total_appearances'] ?? 0) + 1;
                $tally[$numero]['last_appeared_date'] = $resultado->draw_date;
            }
        }

        $hoy = now()->startOfDay();

        return collect($tally)->map(function (array $data, string $numero) use ($totalDraws, $hoy) {
            return (object) [
                'number' => $numero,
                'total_appearances' => $data['total_appearances'],
                'last_appeared_date' => $data['last_appeared_date'],
                'days_since_last_appearance' => $hoy->diffInDays($data['last_appeared_date']),
                'appearance_frequency' => $totalDraws > 0 ? round($data['total_appearances'] / $totalDraws, 4) : 0,
            ];
        })->values();
    }
}
