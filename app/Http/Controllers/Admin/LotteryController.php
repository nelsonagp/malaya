<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LotteryRequest;
use App\Jobs\ScrapeLotteryJob;
use App\Models\Lottery;
use App\Scrapers\ScraperFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LotteryController extends Controller
{
    public function index(): View
    {
        $lotteries = Lottery::orderBy('display_order')->orderBy('name')->get();

        return view('admin.loterias.index', ['lotteries' => $lotteries]);
    }

    public function create(): View
    {
        return view('admin.loterias.form', [
            'lottery' => new Lottery(),
            'scrapers' => array_keys(config('scrapers.registered', [])),
        ]);
    }

    public function store(LotteryRequest $request): RedirectResponse
    {
        $lottery = new Lottery();
        $this->fillFromRequest($lottery, $request);
        $lottery->save();

        return redirect()->route('admin.loterias.index')->with('status', 'Lotería creada correctamente.');
    }

    public function edit(Lottery $lottery): View
    {
        return view('admin.loterias.form', [
            'lottery' => $lottery,
            'scrapers' => array_keys(config('scrapers.registered', [])),
        ]);
    }

    public function update(LotteryRequest $request, Lottery $lottery): RedirectResponse
    {
        $this->fillFromRequest($lottery, $request);
        $lottery->save();

        return redirect()->route('admin.loterias.index')->with('status', 'Lotería actualizada correctamente.');
    }

    public function destroy(Lottery $lottery): RedirectResponse
    {
        $lottery->delete();

        return redirect()->route('admin.loterias.index')->with('status', 'Lotería eliminada.');
    }

    public function logs(Lottery $lottery): View
    {
        $logs = $lottery->scrapeLogs()->latest('created_at')->limit(50)->get();

        return view('admin.loterias.logs', ['lottery' => $lottery, 'logs' => $logs]);
    }

    /**
     * Ejecuta el scraper ahora mismo sin guardar nada — para el botón
     * "Probar scraper" del formulario.
     */
    public function test(Lottery $lottery): JsonResponse
    {
        if (! $lottery->scraper_class) {
            return response()->json(['error' => 'Esta lotería no tiene un scraper asignado.'], 422);
        }

        try {
            $results = ScraperFactory::make($lottery)->run(persist: false);

            return response()->json(['results' => $results]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Despacha el job de scraping real (guarda en BD) — para el botón
     * "Forzar scraping ahora".
     */
    public function forceScrape(Lottery $lottery): RedirectResponse
    {
        if (! $lottery->scraper_class) {
            return back()->with('status', 'Esta lotería no tiene un scraper asignado.');
        }

        ScrapeLotteryJob::dispatch($lottery->slug);

        return back()->with('status', 'Scraping encolado. Revisa los logs en unos segundos.');
    }

    private function fillFromRequest(Lottery $lottery, LotteryRequest $request): void
    {
        $lottery->fill([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'country' => $request->country,
            'country_code' => strtoupper($request->country_code),
            'website_url' => $request->website_url,
            'results_url' => $request->results_url,
            'scraper_class' => $request->scraper_class,
            'scraper_config' => $request->scraper_config ? json_decode($request->scraper_config, true) : null,
            'draw_schedule' => [
                'days' => $request->input('draw_days', []),
                'time' => $request->draw_time,
                'timezone' => $request->draw_timezone ?: 'America/Bogota',
            ],
            'draw_frequency' => $request->draw_frequency,
            'number_count' => $request->number_count,
            'number_range_min' => $request->number_range_min,
            'number_range_max' => $request->number_range_max,
            'has_series' => $request->boolean('has_series'),
            'has_fractions' => $request->boolean('has_fractions'),
            'prize_info' => $request->prize_info,
            'affiliate_url' => $request->affiliate_url,
            'display_order' => $request->display_order ?: 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('logo')) {
            if ($lottery->logo_url) {
                Storage::disk('public')->delete($lottery->logo_url);
            }

            $lottery->logo_url = $request->file('logo')->store('logos', 'public');
        }
    }
}
