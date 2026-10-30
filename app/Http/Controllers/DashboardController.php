<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $metrics = ReportingService::getDashboardMetrics();

        return view('dashboard.index', [
            'kpis' => $metrics['kpis'],
            'chart' => $metrics['chart'],
            'overdueAlerts' => $metrics['overdue_alerts'],
            'recentActivity' => $metrics['recent_activity'],
            'currencySymbol' => $metrics['currency_symbol'],
            'currencyCode' => $metrics['currency_code'],
            'currencyDecimals' => $metrics['currency_decimals'],
        ]);
    }
}
