<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LotteryResultRequest;
use App\Models\Lottery;
use App\Models\LotteryResult;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LotteryResultController extends Controller
{
    public function index(Request $request): View
    {
        $results = LotteryResult::with('lottery')
            ->when($request->filled('lottery_id'), fn ($q) => $q->where('lottery_id', $request->lottery_id))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('draw_date', '>=', $request->desde))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('draw_date', '<=', $request->hasta))
            ->orderByDesc('draw_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.resultados.index', [
            'results' => $results,
            'lotteries' => Lottery::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.resultados.form', [
            'result' => new LotteryResult(),
            'lotteries' => Lottery::orderBy('name')->get(),
        ]);
    }

    public function store(LotteryResultRequest $request): RedirectResponse
    {
        LotteryResult::create($this->mapFromRequest($request));

        return redirect()->route('admin.resultados.index')->with('status', 'Resultado guardado correctamente.');
    }

    public function edit(LotteryResult $resultado): View
    {
        return view('admin.resultados.form', [
            'result' => $resultado,
            'lotteries' => Lottery::orderBy('name')->get(),
        ]);
    }

    public function update(LotteryResultRequest $request, LotteryResult $resultado): RedirectResponse
    {
        $resultado->update($this->mapFromRequest($request));

        return redirect()->route('admin.resultados.index')->with('status', 'Resultado actualizado correctamente.');
    }

    public function destroy(LotteryResult $resultado): RedirectResponse
    {
        $resultado->delete();

        return redirect()->route('admin.resultados.index')->with('status', 'Resultado eliminado.');
    }

    private function mapFromRequest(LotteryResultRequest $request): array
    {
        return [
            'lottery_id' => $request->lottery_id,
            'draw_date' => $request->draw_date,
            'draw_number' => $request->draw_number,
            'numbers' => array_map('trim', explode(',', $request->numbers)),
            'prize_breakdown' => $request->prize_breakdown ? json_decode($request->prize_breakdown, true) : null,
            'jackpot_amount' => $request->jackpot_amount,
            'currency' => $request->currency ?: 'COP',
            'source_url' => $request->source_url,
            'is_verified' => $request->boolean('is_verified'),
            'scraped_at' => null,
        ];
    }
}
