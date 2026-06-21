<?php

use App\Http\Controllers\Admin\AdBannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LotteryController as AdminLotteryController;
use App\Http\Controllers\Admin\LotteryResultController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AdClickController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LotteryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatisticsController;
use App\Support\SitemapBuilder;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/loteria/{lottery:slug}', [LotteryController::class, 'show'])->name('loteria.show');
Route::get('/loteria/{lottery:slug}/afiliado', [LotteryController::class, 'affiliateClick'])->name('loteria.afiliado');

Route::get('/resultados', [ResultsController::class, 'index'])->name('resultados.index');
Route::get('/resultados/exportar', [ResultsController::class, 'exportCsv'])->name('resultados.exportar');

Route::get('/estadisticas', [StatisticsController::class, 'index'])->name('estadisticas.index');
Route::get('/estadisticas/{lottery:slug}', [StatisticsController::class, 'show'])->name('estadisticas.show');

Route::get('/buscar', [SearchController::class, 'index'])->name('buscar.index');

Route::get('/generador', [GeneratorController::class, 'show'])->name('generador.show');
Route::get('/api/generar/{lottery:slug}', [GeneratorController::class, 'generate'])->name('api.generar');

Route::get('/acerca-de', fn () => view('paginas.acerca-de'))->name('paginas.acerca-de');
Route::get('/como-funciona', fn () => view('paginas.como-funciona'))->name('paginas.como-funciona');

Route::get('/sitemap.xml', fn () => Response::make(SitemapBuilder::build(), 200, ['Content-Type' => 'application/xml']))->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');

Route::post('/ad/impresion/{banner}', [AdClickController::class, 'impression'])->name('ad.impresion');
Route::get('/ad/clic/{banner}', [AdClickController::class, 'click'])->name('ad.clic');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/perfil', [ProfileController::class, 'show'])->name('perfil');
    Route::get('/perfil/cuentas', [ProfileController::class, 'accounts'])->name('perfil.cuentas');
    Route::post('/perfil/cuentas/{provider}/desvincular', [ProfileController::class, 'unlink'])->name('perfil.cuentas.desvincular');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('loterias', AdminLotteryController::class)->except(['show'])->parameters(['loterias' => 'lottery']);
    Route::get('/loterias/{lottery}/logs', [AdminLotteryController::class, 'logs'])->name('loterias.logs');
    Route::post('/loterias/{lottery}/probar', [AdminLotteryController::class, 'test'])->name('loterias.test');
    Route::post('/loterias/{lottery}/forzar-scraping', [AdminLotteryController::class, 'forceScrape'])->name('loterias.force-scrape');

    Route::resource('resultados', LotteryResultController::class)->except(['show'])->parameters(['resultados' => 'resultado']);

    Route::resource('publicidad', AdBannerController::class)->except(['show']);

    Route::get('/configuracion', [SettingsController::class, 'show'])->name('configuracion.show');
    Route::put('/configuracion', [SettingsController::class, 'update'])->name('configuracion.update');
    Route::post('/configuracion/usuarios', [SettingsController::class, 'storeAdmin'])->name('configuracion.usuarios.store');
});
