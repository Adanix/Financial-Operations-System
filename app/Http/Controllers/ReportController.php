<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Services\ReportExportService;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BusinessSetting::current();
        $tab = $request->input('tab', 'pnl');
        $allowedTabs = ['pnl', 'tax', 'revenue', 'expenses', 'aging'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'pnl';
        }

        $preset = $request->input('period', 'this_month');
        $range = ReportingService::parseDateRange(
            $preset,
            $request->input('from_date'),
            $request->input('to_date'),
            $settings->timezone
        );

        $asOfDate = $request->input('as_of_date', Carbon::now($settings->timezone)->toDateString());

        $reportData = match ($tab) {
            'tax' => ReportingService::getTaxSummary($range),
            'revenue' => ReportingService::getRevenueReport($range),
            'expenses' => ReportingService::getExpenseReport($range),
            'aging' => ReportingService::getAgedReceivables($asOfDate, $settings->timezone),
            default => ReportingService::getManagementPnl($range),
        };

        return view('reports.index', [
            'tab' => $tab,
            'preset' => $preset,
            'range' => $range,
            'asOfDate' => $asOfDate,
            'reportData' => $reportData,
            'settings' => $settings,
            'currencySymbol' => $settings->currency_symbol,
        ]);
    }

    /**
     * Multi-format export: CSV, XLSX, PDF
     */
    public function export(Request $request): Response|BinaryFileResponse|StreamedResponse
    {
        $settings = BusinessSetting::current();
        $tab = $request->input('tab', 'pnl');
        $allowedTabs = ['pnl', 'tax', 'revenue', 'expenses', 'aging'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'pnl';
        }

        $format = strtolower($request->input('format', 'csv'));
        if (!in_array($format, ['csv', 'xlsx', 'pdf'])) {
            $format = 'csv';
        }

        $preset = $request->input('period', 'this_month');
        $range = ReportingService::parseDateRange(
            $preset,
            $request->input('from_date'),
            $request->input('to_date'),
            $settings->timezone
        );

        $asOfDate = $request->input('as_of_date', Carbon::now($settings->timezone)->toDateString());

        return ReportExportService::export($tab, $format, $range, $asOfDate, $settings);
    }
}
