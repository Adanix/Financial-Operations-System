<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

    public function export(Request $request): StreamedResponse
    {
        $settings = BusinessSetting::current();
        $tab = $request->input('tab', 'pnl');
        $preset = $request->input('period', 'this_month');
        $range = ReportingService::parseDateRange(
            $preset,
            $request->input('from_date'),
            $request->input('to_date'),
            $settings->timezone
        );
        $asOfDate = $request->input('as_of_date', Carbon::now($settings->timezone)->toDateString());

        $filename = "SoloLedger_{$tab}_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tab, $range, $asOfDate, $settings) {
            $handle = fopen('php://output', 'w');

            // Sanitize function to prevent CSV Formula Injection
            $sanitize = function ($value) {
                if ($value === null) return '';
                $str = (string) $value;
                if (preg_match('/^[=\+\-@\t\r]/', $str)) {
                    return "'" . $str;
                }
                return $str;
            };

            if ($tab === 'pnl') {
                $pnl = ReportingService::getManagementPnl($range);
                fputcsv($handle, ['Management P&L Report', "Period: {$range['from_date']} to {$range['to_date']}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Metric', "Amount ({$settings->currency_code})"]);
                fputcsv($handle, ['Net Operating Revenue', $sanitize($pnl['net_operating_revenue'])]);
                fputcsv($handle, ['Operating Expenses', $sanitize($pnl['operating_expenses'])]);
                fputcsv($handle, ['Net Operating Profit', $sanitize($pnl['net_operating_profit'])]);
                fputcsv($handle, []);
                fputcsv($handle, ['Operating Expenses Breakdown by Category']);
                foreach ($pnl['category_breakdown'] as $cat => $amt) {
                    fputcsv($handle, [$sanitize($cat), $sanitize($amt)]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['Cash Memo']);
                fputcsv($handle, ['Cash Collected', $sanitize($pnl['cash_memo']['cash_collected'])]);
                fputcsv($handle, ['Cash Expenses Paid', $sanitize($pnl['cash_memo']['cash_expenses'])]);
                fputcsv($handle, ['Net Cash Flow', $sanitize($pnl['cash_memo']['net_cash_flow'])]);
            } elseif ($tab === 'tax') {
                $tax = ReportingService::getTaxSummary($range);
                fputcsv($handle, ['Tax Summary Report', "Period: {$range['from_date']} to {$range['to_date']}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Tax Rate Name', 'Rate %', 'Taxable Sales', 'Output Tax Charged', 'Taxable Purchases', 'Recoverable Input Tax', 'Estimated Net Position']);
                foreach ($tax['rates'] as $r) {
                    fputcsv($handle, [
                        $sanitize($r['name']),
                        $sanitize($r['rate']),
                        $sanitize($r['taxable_sales']),
                        $sanitize($r['output_tax']),
                        $sanitize($r['taxable_purchases']),
                        $sanitize($r['recoverable_input_tax']),
                        $sanitize($r['net_position']),
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['Total Output Tax', $sanitize($tax['total_output_tax'])]);
                fputcsv($handle, ['Total Recoverable Input Tax', $sanitize($tax['total_recoverable_input_tax'])]);
                fputcsv($handle, ['Estimated Net Tax Position', $sanitize($tax['estimated_net_tax_position'])]);
            } elseif ($tab === 'revenue') {
                $rev = ReportingService::getRevenueReport($range);
                fputcsv($handle, ['Revenue & Sales Report', "Period: {$range['from_date']} to {$range['to_date']}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Invoice #', 'Customer', 'Issue Date', 'Due Date', 'Status', 'Invoiced Total', 'Tax', 'Paid', 'Outstanding']);
                foreach ($rev['invoices'] as $inv) {
                    fputcsv($handle, [
                        $sanitize($inv->invoice_number),
                        $sanitize($inv->customer?->name),
                        $sanitize($inv->issue_date->format('Y-m-d')),
                        $sanitize($inv->due_date->format('Y-m-d')),
                        $sanitize($inv->display_status),
                        $sanitize($inv->total),
                        $sanitize($inv->tax_total),
                        $sanitize($inv->amount_paid),
                        $sanitize($inv->outstanding_balance),
                    ]);
                }
            } elseif ($tab === 'expenses') {
                $exp = ReportingService::getExpenseReport($range);
                fputcsv($handle, ['Expense Breakdown Report', "Period: {$range['from_date']} to {$range['to_date']}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Date', 'Vendor', 'Category', 'Description', 'Pricing', 'Net', 'Tax', 'Gross', 'Recoverable', 'Payment Status']);
                foreach ($exp['expenses'] as $e) {
                    fputcsv($handle, [
                        $sanitize($e->expense_date->format('Y-m-d')),
                        $sanitize($e->vendor),
                        $sanitize($e->category?->name),
                        $sanitize($e->description),
                        $sanitize($e->pricing_mode),
                        $sanitize($e->net_amount),
                        $sanitize($e->tax_amount),
                        $sanitize($e->gross_amount),
                        $sanitize($e->tax_recoverable ? 'Yes' : 'No'),
                        $sanitize($e->payment_status),
                    ]);
                }
            } elseif ($tab === 'aging') {
                $aging = ReportingService::getAgedReceivables($asOfDate, $settings->timezone);
                fputcsv($handle, ['Aged Receivables Report', "As of Date: {$asOfDate}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Invoice #', 'Customer', 'Due Date', 'Total', 'Paid As of Date', 'Balance', 'Days Overdue', 'Aging Bucket']);
                foreach ($aging['invoices'] as $row) {
                    fputcsv($handle, [
                        $sanitize($row['invoice_number']),
                        $sanitize($row['customer_name']),
                        $sanitize($row['due_date']),
                        $sanitize($row['total']),
                        $sanitize($row['effective_paid']),
                        $sanitize($row['balance']),
                        $sanitize($row['days_overdue']),
                        $sanitize($row['bucket']),
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
