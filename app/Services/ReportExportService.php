<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Dispatch report export by requested format (csv, xlsx, pdf).
     */
    public static function export(
        string $tab,
        string $format,
        array $range,
        string $asOfDate,
        BusinessSetting $settings
    ): Response|BinaryFileResponse|StreamedResponse {
        return match ($format) {
            'xlsx' => self::exportXlsx($tab, $range, $asOfDate, $settings),
            'pdf' => self::exportPdf($tab, $range, $asOfDate, $settings),
            default => self::exportCsv($tab, $range, $asOfDate, $settings),
        };
    }

    /**
     * Export report data as standard CSV.
     */
    public static function exportCsv(
        string $tab,
        array $range,
        string $asOfDate,
        BusinessSetting $settings
    ): StreamedResponse {
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

            // Formula injection defense
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

    /**
     * Export report data as Microsoft Excel (.xlsx) workbook using OpenSpout.
     */
    public static function exportXlsx(
        string $tab,
        array $range,
        string $asOfDate,
        BusinessSetting $settings
    ): BinaryFileResponse {
        $filename = "SoloLedger_{$tab}_" . now()->format('Ymd_His') . ".xlsx";
        $tempPath = tempnam(sys_get_temp_dir(), 'solo_xlsx_');

        $writer = new Writer();
        $writer->openToFile($tempPath);

        $headerStyle = (new Style())->withFontBold(true)->withBackgroundColor('F1F4F2');
        $titleStyle = (new Style())->withFontBold(true)->withFontSize(14);
        $sectionStyle = (new Style())->withFontBold(true)->withFontSize(11);

        if ($tab === 'pnl') {
            $pnl = ReportingService::getManagementPnl($range);
            $writer->addRow(Row::fromValuesWithStyle(["Management Profit & Loss Report - {$settings->business_name}"], $titleStyle));
            $writer->addRow(Row::fromValues(["Period: {$range['from_date']} to {$range['to_date']} | Currency: {$settings->currency_code} ({$settings->currency_symbol})"]));
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle(['Financial Performance Metric', 'Amount (' . $settings->currency_code . ')'], $headerStyle));
            $writer->addRow(Row::fromValues(['Net Operating Revenue (Excluding Tax)', (float) $pnl['net_operating_revenue']]));
            $writer->addRow(Row::fromValues(['Operating Expenses (Excluding Recoverable Tax)', (float) $pnl['operating_expenses']]));
            $writer->addRow(Row::fromValues(['Net Operating Profit', (float) $pnl['net_operating_profit']]));
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle(['Operating Expenses Breakdown by Category', 'Amount (' . $settings->currency_code . ')'], $headerStyle));
            foreach ($pnl['category_breakdown'] as $cat => $amt) {
                $writer->addRow(Row::fromValues([$cat, (float) $amt]));
            }
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle(['Cash Flow Memo', 'Amount (' . $settings->currency_code . ')'], $headerStyle));
            $writer->addRow(Row::fromValues(['Cash Collected from Invoices', (float) $pnl['cash_memo']['cash_collected']]));
            $writer->addRow(Row::fromValues(['Cash Paid for Expenses', (float) $pnl['cash_memo']['cash_expenses']]));
            $writer->addRow(Row::fromValues(['Net Operating Cash Flow', (float) $pnl['cash_memo']['net_cash_flow']]));

        } elseif ($tab === 'tax') {
            $tax = ReportingService::getTaxSummary($range);
            $writer->addRow(Row::fromValuesWithStyle(["Tax Summary Report - {$settings->business_name}"], $titleStyle));
            $writer->addRow(Row::fromValues(["Period: {$range['from_date']} to {$range['to_date']} | Currency: {$settings->currency_code}"]));
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle([
                'Tax Bracket', 'Rate (%)', 'Taxable Sales', 'Output Tax Charged', 'Taxable Purchases', 'Recoverable Input Tax', 'Net Tax Position'
            ], $headerStyle));

            foreach ($tax['rates'] as $r) {
                $writer->addRow(Row::fromValues([
                    $r['name'],
                    (float) $r['rate'],
                    (float) $r['taxable_sales'],
                    (float) $r['output_tax'],
                    (float) $r['taxable_purchases'],
                    (float) $r['recoverable_input_tax'],
                    (float) $r['net_position'],
                ]));
            }
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle(['Summary Position', 'Amount (' . $settings->currency_code . ')'], $headerStyle));
            $writer->addRow(Row::fromValues(['Total Output Tax Collected', (float) $tax['total_output_tax']]));
            $writer->addRow(Row::fromValues(['Total Recoverable Input Tax Claimed', (float) $tax['total_recoverable_input_tax']]));
            $writer->addRow(Row::fromValues(['Estimated Net Tax Position', (float) $tax['estimated_net_tax_position']]));

        } elseif ($tab === 'revenue') {
            $rev = ReportingService::getRevenueReport($range);
            $writer->addRow(Row::fromValuesWithStyle(["Revenue & Sales Ledger - {$settings->business_name}"], $titleStyle));
            $writer->addRow(Row::fromValues(["Period: {$range['from_date']} to {$range['to_date']} | Currency: {$settings->currency_code}"]));
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle([
                'Invoice #', 'Customer Name', 'Issue Date', 'Due Date', 'Status', 'Invoiced Total', 'Tax Total', 'Amount Paid', 'Outstanding Balance'
            ], $headerStyle));

            foreach ($rev['invoices'] as $inv) {
                $writer->addRow(Row::fromValues([
                    $inv->invoice_number,
                    $inv->customer?->name ?? 'Unknown',
                    $inv->issue_date->format('Y-m-d'),
                    $inv->due_date->format('Y-m-d'),
                    ucwords(str_replace('_', ' ', $inv->status)),
                    (float) $inv->total,
                    (float) $inv->tax_total,
                    (float) $inv->amount_paid,
                    (float) $inv->outstanding_balance,
                ]));
            }

        } elseif ($tab === 'expenses') {
            $exp = ReportingService::getExpenseReport($range);
            $writer->addRow(Row::fromValuesWithStyle(["Expense Breakdown Ledger - {$settings->business_name}"], $titleStyle));
            $writer->addRow(Row::fromValues(["Period: {$range['from_date']} to {$range['to_date']} | Currency: {$settings->currency_code}"]));
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle([
                'Expense Date', 'Vendor / Payee', 'Category', 'Description', 'Pricing Mode', 'Net Amount', 'Tax Amount', 'Gross Total', 'Tax Recoverable', 'Payment Status'
            ], $headerStyle));

            foreach ($exp['expenses'] as $e) {
                $writer->addRow(Row::fromValues([
                    $e->expense_date->format('Y-m-d'),
                    $e->vendor,
                    $e->category?->name ?? 'Uncategorized',
                    $e->description,
                    ucfirst($e->pricing_mode),
                    (float) $e->net_amount,
                    (float) $e->tax_amount,
                    (float) $e->gross_amount,
                    $e->tax_recoverable ? 'Yes' : 'No',
                    ucfirst($e->payment_status),
                ]));
            }

        } elseif ($tab === 'aging') {
            $aging = ReportingService::getAgedReceivables($asOfDate, $settings->timezone);
            $writer->addRow(Row::fromValuesWithStyle(["Aged Receivables Ledger - {$settings->business_name}"], $titleStyle));
            $writer->addRow(Row::fromValues(["As-of Date: {$asOfDate} | Currency: {$settings->currency_code}"]));
            $writer->addRow(Row::fromValues([]));

            $writer->addRow(Row::fromValuesWithStyle([
                'Invoice #', 'Customer Name', 'Issue Date', 'Due Date', 'Total Invoiced', 'Paid As of Date', 'Balance Due', 'Days Overdue', 'Aging Bracket'
            ], $headerStyle));

            foreach ($aging['invoices'] as $row) {
                $writer->addRow(Row::fromValues([
                    $row['invoice_number'],
                    $row['customer_name'],
                    $row['issue_date'],
                    $row['due_date'],
                    (float) $row['total'],
                    (float) $row['effective_paid'],
                    (float) $row['balance'],
                    $row['days_overdue'],
                    strtoupper(str_replace('_', '-', $row['bucket'])),
                ]));
            }
        }

        $writer->close();

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export report data as publication-ready A4 PDF via Dompdf.
     */
    public static function exportPdf(
        string $tab,
        array $range,
        string $asOfDate,
        BusinessSetting $settings
    ): Response {
        $filename = "SoloLedger_{$tab}_" . now()->format('Ymd_His') . ".pdf";

        $reportData = match ($tab) {
            'tax' => ReportingService::getTaxSummary($range),
            'revenue' => ReportingService::getRevenueReport($range),
            'expenses' => ReportingService::getExpenseReport($range),
            'aging' => ReportingService::getAgedReceivables($asOfDate, $settings->timezone),
            default => ReportingService::getManagementPnl($range),
        };

        $pdf = Pdf::loadView('reports.pdf', [
            'tab' => $tab,
            'reportData' => $reportData,
            'range' => $range,
            'asOfDate' => $asOfDate,
            'settings' => $settings,
            'currencySymbol' => $settings->currency_symbol,
            'printedAt' => now()->timezone($settings->timezone)->format('Y-m-d H:i:s'),
        ]);

        // Wide tables benefit from Landscape
        $orientation = in_array($tab, ['revenue', 'expenses', 'aging']) ? 'landscape' : 'portrait';
        $pdf->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}
