<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use App\Models\LotteryResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultsController extends Controller
{
    public function index(Request $request): View
    {
        $resultados = $this->filteredQuery($request)
            ->with('lottery')
            ->orderByDesc('draw_date')
            ->paginate(20)
            ->withQueryString();

        $lotteries = Lottery::query()->where('is_active', true)->orderBy('display_order')->get();

        return view('resultados.index', [
            'resultados' => $resultados,
            'lotteries' => $lotteries,
            'filtros' => $request->only(['pais', 'loteria', 'desde', 'hasta']),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless(Auth::check(), 403, 'Debes iniciar sesión para exportar resultados.');

        $resultados = $this->filteredQuery($request)->with('lottery')->orderByDesc('draw_date')->get();

        return response()->streamDownload(function () use ($resultados) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Lotería', 'Fecha', 'Resultado', 'Serie']);

            foreach ($resultados as $resultado) {
                fputcsv($handle, [
                    $resultado->lottery->name,
                    $resultado->draw_date->toDateString(),
                    $resultado->numbers[0] ?? '',
                    $resultado->lottery->has_series ? ($resultado->numbers[1] ?? '') : '',
                ]);
            }

            fclose($handle);
        }, 'resultados-malaya.csv', ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request)
    {
        $query = LotteryResult::query()->whereHas('lottery', fn ($q) => $q->where('is_active', true));

        if ($pais = $request->query('pais')) {
            $query->whereHas('lottery', function ($q) use ($pais) {
                if ($pais === 'colombia') {
                    $q->where('country_code', 'CO');
                } elseif ($pais === 'internacional') {
                    $q->where('country_code', '!=', 'CO');
                }
            });
        }

        if ($slug = $request->query('loteria')) {
            $query->whereHas('lottery', fn ($q) => $q->where('slug', $slug));
        }

        if ($desde = $request->query('desde')) {
            $query->whereDate('draw_date', '>=', $desde);
        }

        if ($hasta = $request->query('hasta')) {
            $query->whereDate('draw_date', '<=', $hasta);
        }

        return $query;
    }
}
