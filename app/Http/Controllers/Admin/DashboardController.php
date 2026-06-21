<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdBanner;
use App\Models\Lottery;
use App\Models\LotteryResult;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'activeLotteries' => Lottery::where('is_active', true)->count(),
            'inactiveLotteries' => Lottery::where('is_active', false)->count(),
            'totalResults' => LotteryResult::count(),
            'totalUsers' => User::count(),
            'totalAdClicks' => AdBanner::sum('click_count'),
            'lotteries' => Lottery::orderBy('name')->get(['id', 'name', 'last_scraped_at', 'scrape_error']),
        ]);
    }
}
