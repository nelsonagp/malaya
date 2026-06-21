<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneratorController extends Controller
{
    public function show(): View
    {
        $lotteries = Lottery::query()->where('is_active', true)->orderBy('display_order')->get();

        return view('generador.index', ['lotteries' => $lotteries]);
    }

    public function generate(Request $request, Lottery $lottery): JsonResponse
    {
        abort_unless($lottery->is_active, 404);

        $modo = $request->query('modo') === 'estadistico' ? 'estadistico' : 'aleatorio';
        $cantidad = max(1, min(5, (int) $request->query('cantidad', 1)));

        $combinaciones = [];

        for ($i = 0; $i < $cantidad; $i++) {
            $combinaciones[] = $modo === 'estadistico'
                ? $this->generarBasadoEnEstadisticas($lottery)
                : $this->generarAleatorio($lottery);
        }

        return response()->json([
            'loteria' => $lottery->name,
            'modo' => $modo,
            'combinaciones' => $combinaciones,
        ]);
    }

    private function generarAleatorio(Lottery $lottery): array
    {
        if ($lottery->has_series) {
            $numero = random_int($lottery->number_range_min, $lottery->number_range_max);

            return [str_pad((string) $numero, $lottery->number_count, '0', STR_PAD_LEFT)];
        }

        $rango = range($lottery->number_range_min, $lottery->number_range_max);
        shuffle($rango);
        $numeros = array_slice($rango, 0, $lottery->number_count);
        sort($numeros);

        return array_map('strval', $numeros);
    }

    private function generarBasadoEnEstadisticas(Lottery $lottery): array
    {
        $stats = $lottery->numberStatistics()->where('total_appearances', '>', 0)->get();

        if ($lottery->has_series) {
            $stats = $stats->filter(fn ($stat) => strlen($stat->number) === $lottery->number_count);
        }

        if ($stats->count() < ($lottery->has_series ? 1 : $lottery->number_count)) {
            return $this->generarAleatorio($lottery);
        }

        $pool = [];

        foreach ($stats as $stat) {
            for ($i = 0, $peso = max(1, $stat->total_appearances); $i < $peso; $i++) {
                $pool[] = $stat->number;
            }
        }

        if ($lottery->has_series) {
            return [$pool[array_rand($pool)]];
        }

        $seleccionados = [];
        $intentos = 0;

        while (count($seleccionados) < $lottery->number_count && $intentos < 1000) {
            $candidato = $pool[array_rand($pool)];

            if (! in_array($candidato, $seleccionados, true)) {
                $seleccionados[] = $candidato;
            }

            $intentos++;
        }

        sort($seleccionados);

        return $seleccionados;
    }
}
