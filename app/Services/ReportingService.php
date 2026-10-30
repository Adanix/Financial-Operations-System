<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Parse date range based on preset and timezone.
     */
    public static function parseDateRange(
        string $preset = 'this_month',
        ?string $customFrom = null,
        ?string $customTo = null,
        ?string $timezone = null
    ): array {
        $tz = $timezone ?: BusinessSetting::current()->timezone;
        $now = Carbon::now($tz);

        switch ($preset) {
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $start = $now->copy()->firstOfQuarter()->startOfDay();
                $end = $now->copy()->lastOfQuarter()->endOfDay();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'last_year':
                $start = $now->copy()->subYear()->startOfYear();
                $end = $now->copy()->subYear()->endOfYear();
                break;
            case 'all_time':
                $start = Carbon::parse('2000-01-01', $tz)->startOfDay();
                $end = Carbon::parse('2099-12-31', $tz)->endOfDay();
                break;
            case 'custom':
                if ($customFrom && $customTo) {
                    $start = Carbon::parse($customFrom, $tz)->startOfDay();
                    $end = Carbon::parse($customTo, $tz)->endOfDay();
                    if ($start->gt($end)) {
                        $temp = $start;
                        $start = $end;
                        $end = $temp;
                    }
                } else {
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                }
                break;
            case 'this_month':
            default:
                $preset = 'this_month';
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        return [
            'preset' => $preset,
            'from_date' => $start->toDateString(),
            'to_date' => $end->toDateString(),
            'from_label' => $start->format('M d, Y'),
            'to_label' => $end->format('M d, Y'),
            'timezone' => $tz,
        ];
    }

    /**
     * 1. Management P&L Report
     */
    public static function getManagementPnl(array $range): array
    {
        $decimals = BusinessSetting::current()->currency_decimals ?? 2;
        $fromDate = $range['from_date'];
        $toDate = $range['to_date'];

        // Qualifying invoices: issued, partially_paid, paid within issue_date range
        $invoices = Invoice::with('items')
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->get();

        $netOperatingRevenue = '0.00';
        foreach ($invoices as $inv) {
            $invDecimals = $inv->currency_decimals ?? $decimals;
            if ($inv->pricing_mode === 'inclusive') {
                $rev = DecimalMath::sub((string)$inv->taxable_subtotal, (string)$inv->tax_total, $invDecimals);
            } else {
                $rev = (string)$inv->taxable_subtotal;
            }
            $netOperatingRevenue = DecimalMath::add($netOperatingRevenue, $rev, $decimals);
        }

        // Expenses within expense_date range
        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->get();

        $categoryBreakdown = [];
        $operatingExpenses = '0.00';

        foreach ($expenses as $exp) {
            $catName = $exp->category ? $exp->category->name : 'Uncategorized';
            $pnlAmount = $exp->expense_for_pnl;

            $operatingExpenses = DecimalMath::add($operatingExpenses, $pnlAmount, $decimals);

            if (!isset($categoryBreakdown[$catName])) {
                $categoryBreakdown[$catName] = '0.00';
            }
            $categoryBreakdown[$catName] = DecimalMath::add($categoryBreakdown[$catName], $pnlAmount, $decimals);
        }

        // Sort categories by amount descending
        uasort($categoryBreakdown, fn($a, $b) => DecimalMath::comp($b, $a));

        $netOperatingProfit = DecimalMath::sub($netOperatingRevenue, $operatingExpenses, $decimals);

        // Cash Memo
        $cashCollected = (string) Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('amount');
        $cashCollected = DecimalMath::round($cashCollected, $decimals);

        $cashExpenses = (string) Expense::where('payment_status', 'paid')
            ->whereNotNull('payment_date')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->sum('gross_amount');
        $cashExpenses = DecimalMath::round($cashExpenses, $decimals);

        return [
            'period' => $range,
            'net_operating_revenue' => $netOperatingRevenue,
            'operating_expenses' => $operatingExpenses,
            'net_operating_profit' => $netOperatingProfit,
            'category_breakdown' => $categoryBreakdown,
            'cash_memo' => [
                'cash_collected' => $cashCollected,
                'cash_expenses' => $cashExpenses,
                'net_cash_flow' => DecimalMath::sub($cashCollected, $cashExpenses, $decimals),
            ],
            'invoices_count' => $invoices->count(),
            'expenses_count' => $expenses->count(),
        ];
    }

    /**
     * 2. Tax Summary Report
     */
    public static function getTaxSummary(array $range): array
    {
        $decimals = BusinessSetting::current()->currency_decimals ?? 2;
        $fromDate = $range['from_date'];
        $toDate = $range['to_date'];

        // Output Tax from qualifying invoices
        $invoiceItems = InvoiceItem::whereHas('invoice', function ($q) use ($fromDate, $toDate) {
            $q->whereIn('status', ['issued', 'partially_paid', 'paid'])
              ->whereBetween('issue_date', [$fromDate, $toDate]);
        })->get();

        $ratesMap = [];

        foreach ($invoiceItems as $item) {
            $key = $item->tax_name . ' (' . DecimalMath::round($item->tax_rate, 2) . '%)';
            if (!isset($ratesMap[$key])) {
                $ratesMap[$key] = [
                    'name' => $item->tax_name,
                    'rate' => DecimalMath::round($item->tax_rate, 2),
                    'taxable_sales' => '0.00',
                    'output_tax' => '0.00',
                    'taxable_purchases' => '0.00',
                    'recoverable_input_tax' => '0.00',
                ];
            }
            $ratesMap[$key]['taxable_sales'] = DecimalMath::add($ratesMap[$key]['taxable_sales'], $item->taxable_amount, $decimals);
            $ratesMap[$key]['output_tax'] = DecimalMath::add($ratesMap[$key]['output_tax'], $item->tax_amount, $decimals);
        }

        // Recoverable input tax from expenses
        $expenses = Expense::whereBetween('expense_date', [$fromDate, $toDate])
            ->where('tax_recoverable', true)
            ->get();

        foreach ($expenses as $exp) {
            $rateStr = DecimalMath::round($exp->tax_rate, 2);
            $key = ($exp->tax_name ?: 'Expense Tax') . ' (' . $rateStr . '%)';
            if (!isset($ratesMap[$key])) {
                $ratesMap[$key] = [
                    'name' => $exp->tax_name ?: 'Expense Tax',
                    'rate' => $rateStr,
                    'taxable_sales' => '0.00',
                    'output_tax' => '0.00',
                    'taxable_purchases' => '0.00',
                    'recoverable_input_tax' => '0.00',
                ];
            }
            $ratesMap[$key]['taxable_purchases'] = DecimalMath::add($ratesMap[$key]['taxable_purchases'], $exp->net_amount, $decimals);
            $ratesMap[$key]['recoverable_input_tax'] = DecimalMath::add($ratesMap[$key]['recoverable_input_tax'], $exp->tax_amount, $decimals);
        }

        $totalOutputTax = '0.00';
        $totalInputTax = '0.00';

        foreach ($ratesMap as $k => $data) {
            $totalOutputTax = DecimalMath::add($totalOutputTax, $data['output_tax'], $decimals);
            $totalInputTax = DecimalMath::add($totalInputTax, $data['recoverable_input_tax'], $decimals);
            $ratesMap[$k]['net_position'] = DecimalMath::sub($data['output_tax'], $data['recoverable_input_tax'], $decimals);
        }

        $netTaxPosition = DecimalMath::sub($totalOutputTax, $totalInputTax, $decimals);

        return [
            'period' => $range,
            'rates' => array_values($ratesMap),
            'total_output_tax' => $totalOutputTax,
            'total_recoverable_input_tax' => $totalInputTax,
            'estimated_net_tax_position' => $netTaxPosition,
        ];
    }

    /**
     * 3. Revenue & Sales Report
     */
    public static function getRevenueReport(array $range): array
    {
        $decimals = BusinessSetting::current()->currency_decimals ?? 2;
        $fromDate = $range['from_date'];
        $toDate = $range['to_date'];

        $invoices = Invoice::with('customer')
            ->whereIn('status', ['issued', 'partially_paid', 'paid'])
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->orderBy('issue_date', 'desc')
            ->get();

        $totalInvoiced = '0.00';
        $totalTax = '0.00';
        $totalNetSales = '0.00';
        $totalPaid = '0.00';
        $totalOutstanding = '0.00';

        $customerMap = [];

        foreach ($invoices as $inv) {
            $totalInvoiced = DecimalMath::add($totalInvoiced, (string)$inv->total, $decimals);
            $totalTax = DecimalMath::add($totalTax, (string)$inv->tax_total, $decimals);
            $totalPaid = DecimalMath::add($totalPaid, (string)$inv->amount_paid, $decimals);
            $totalOutstanding = DecimalMath::add($totalOutstanding, $inv->outstanding_balance, $decimals);

            $netSales = ($inv->pricing_mode === 'inclusive')
                ? DecimalMath::sub((string)$inv->taxable_subtotal, (string)$inv->tax_total, $decimals)
                : (string)$inv->taxable_subtotal;

            $totalNetSales = DecimalMath::add($totalNetSales, $netSales, $decimals);

            $cId = $inv->customer_id;
            $cName = $inv->customer ? $inv->customer->name : 'Unknown';
            if (!isset($customerMap[$cId])) {
                $customerMap[$cId] = [
                    'id' => $cId,
                    'name' => $cName,
                    'invoice_count' => 0,
                    'net_sales' => '0.00',
                    'total_invoiced' => '0.00',
                ];
            }
            $customerMap[$cId]['invoice_count']++;
            $customerMap[$cId]['net_sales'] = DecimalMath::add($customerMap[$cId]['net_sales'], $netSales, $decimals);
            $customerMap[$cId]['total_invoiced'] = DecimalMath::add($customerMap[$cId]['total_invoiced'], (string)$inv->total, $decimals);
        }

        // Sort customers by net sales descending
        usort($customerMap, fn($a, $b) => DecimalMath::comp($b['net_sales'], $a['net_sales']));

        return [
            'period' => $range,
            'total_invoiced' => $totalInvoiced,
            'total_tax' => $totalTax,
            'total_net_sales' => $totalNetSales,
            'total_paid' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'invoices' => $invoices,
            'top_customers' => array_slice($customerMap, 0, 10),
        ];
    }

    /**
     * 4. Expense Breakdown Report
     */
    public static function getExpenseReport(array $range): array
    {
        $decimals = BusinessSetting::current()->currency_decimals ?? 2;
        $fromDate = $range['from_date'];
        $toDate = $range['to_date'];

        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalPnlExpenses = '0.00';
        $totalGrossExpenses = '0.00';
        $totalPaidCash = '0.00';
        $categoryMap = [];

        foreach ($expenses as $exp) {
            $catName = $exp->category ? $exp->category->name : 'Uncategorized';
            $pnlAmt = $exp->expense_for_pnl;

            $totalPnlExpenses = DecimalMath::add($totalPnlExpenses, $pnlAmt, $decimals);
            $totalGrossExpenses = DecimalMath::add($totalGrossExpenses, (string)$exp->gross_amount, $decimals);

            if ($exp->payment_status === 'paid') {
                $totalPaidCash = DecimalMath::add($totalPaidCash, (string)$exp->gross_amount, $decimals);
            }

            if (!isset($categoryMap[$catName])) {
                $categoryMap[$catName] = [
                    'category' => $catName,
                    'count' => 0,
                    'amount' => '0.00',
                    'percentage' => '0.00',
                ];
            }
            $categoryMap[$catName]['count']++;
            $categoryMap[$catName]['amount'] = DecimalMath::add($categoryMap[$catName]['amount'], $pnlAmt, $decimals);
        }

        // Compute percentage share
        if (DecimalMath::comp($totalPnlExpenses, '0') > 0) {
            foreach ($categoryMap as $k => $cdata) {
                $pct = DecimalMath::div(DecimalMath::mul($cdata['amount'], '100', 4), $totalPnlExpenses, 2);
                $categoryMap[$k]['percentage'] = $pct;
            }
        }

        usort($categoryMap, fn($a, $b) => DecimalMath::comp($b['amount'], $a['amount']));

        return [
            'period' => $range,
            'total_pnl_expenses' => $totalPnlExpenses,
            'total_gross_expenses' => $totalGrossExpenses,
            'total_paid_cash' => $totalPaidCash,
            'category_breakdown' => $categoryMap,
            'expenses' => $expenses,
        ];
    }

    /**
     * 5. Aged Receivables Report
     * As of selected date.
     */
    public static function getAgedReceivables(?string $asOfDate = null, ?string $timezone = null): array
    {
        $settings = BusinessSetting::current();
        $tz = $timezone ?: $settings->timezone;
        $decimals = $settings->currency_decimals ?? 2;
        $asOf = $asOfDate ? Carbon::parse($asOfDate, $tz) : Carbon::now($tz);
        $asOfDateStr = $asOf->toDateString();

        // Qualifying invoices: issued on or before asOfDate, and NOT voided on or before asOfDate
        $invoices = Invoice::with(['customer', 'payments'])
            ->whereDate('issue_date', '<=', $asOfDateStr)
            ->where(function ($q) use ($asOfDateStr) {
                $q->whereNull('voided_at')
                  ->orWhere('voided_at', '>', $asOfDateStr . ' 23:59:59');
            })
            ->where('status', '!=', 'draft')
            ->get();

        $buckets = [
            'current' => '0.00',
            '1_30' => '0.00',
            '31_60' => '0.00',
            '61_90' => '0.00',
            '90_plus' => '0.00',
        ];

        $totalReceivables = '0.00';
        $reportInvoices = [];

        foreach ($invoices as $inv) {
            // Calculate effective completed payments as of date
            $effectivePaid = '0.00';
            foreach ($inv->payments as $payment) {
                $pDate = is_string($payment->payment_date) ? $payment->payment_date : $payment->payment_date->toDateString();
                if ($pDate <= $asOfDateStr) {
                    // Check reversal
                    if ($payment->status === 'reversed') {
                        $reversedAt = $payment->reversed_at ? $payment->reversed_at->timezone($tz)->toDateTimeString() : null;
                        if ($reversedAt && $reversedAt <= ($asOfDateStr . ' 23:59:59')) {
                            // Already reversed as of that date, ignore payment
                            continue;
                        }
                    }
                    $effectivePaid = DecimalMath::add($effectivePaid, (string)$payment->amount, $decimals);
                }
            }

            $historicalBalance = DecimalMath::max('0.00', DecimalMath::sub((string)$inv->total, $effectivePaid, $decimals), $decimals);

            if (DecimalMath::comp($historicalBalance, '0') <= 0) {
                continue; // Paid off as of that date
            }

            $issueDateStr = is_string($inv->issue_date) ? $inv->issue_date : $inv->issue_date->toDateString();
            $dueDateStr = is_string($inv->due_date) ? $inv->due_date : $inv->due_date->toDateString();
            $dueDate = Carbon::parse($dueDateStr, $tz);

            $bucketKey = 'current';
            $daysOverdue = 0;

            if ($dueDate->lt($asOf)) {
                $daysOverdue = $dueDate->diffInDays($asOf);
                if ($daysOverdue >= 1 && $daysOverdue <= 30) {
                    $bucketKey = '1_30';
                } elseif ($daysOverdue >= 31 && $daysOverdue <= 60) {
                    $bucketKey = '31_60';
                } elseif ($daysOverdue >= 61 && $daysOverdue <= 90) {
                    $bucketKey = '61_90';
                } elseif ($daysOverdue > 90) {
                    $bucketKey = '90_plus';
                }
            }

            $buckets[$bucketKey] = DecimalMath::add($buckets[$bucketKey], $historicalBalance, $decimals);
            $totalReceivables = DecimalMath::add($totalReceivables, $historicalBalance, $decimals);

            $reportInvoices[] = [
                'invoice' => $inv,
                'customer_name' => $inv->customer ? $inv->customer->name : 'Unknown',
                'invoice_number' => $inv->invoice_number,
                'issue_date' => $issueDateStr,
                'due_date' => $dueDateStr,
                'total' => (string)$inv->total,
                'effective_paid' => $effectivePaid,
                'balance' => $historicalBalance,
                'days_overdue' => $daysOverdue,
                'bucket' => $bucketKey,
            ];
        }

        return [
            'as_of_date' => $asOfDateStr,
            'as_of_label' => $asOf->format('M d, Y'),
            'buckets' => $buckets,
            'total_receivables' => $totalReceivables,
            'invoices' => $reportInvoices,
        ];
    }

    /**
     * Dashboard KPIs and 6-month trend
     */
    public static function getDashboardMetrics(): array
    {
        $settings = BusinessSetting::current();
        $tz = $settings->timezone;
        $decimals = $settings->currency_decimals ?? 2;
        $now = Carbon::now($tz);

        // Period: This Month
        $currentMonthRange = self::parseDateRange('this_month', null, null, $tz);
        $pnl = self::getManagementPnl($currentMonthRange);

        // Point-in-time Receivables: Today
        $aging = self::getAgedReceivables($now->toDateString(), $tz);
        $outstandingReceivables = $aging['total_receivables'];
        $overdueReceivables = DecimalMath::sub(
            $aging['total_receivables'],
            $aging['buckets']['current'],
            $decimals
        );

        // 6-Month Trend Data for Chart.js
        $chartLabels = [];
        $revenueSeries = [];
        $expenseSeries = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd = $now->copy()->subMonths($i)->endOfMonth();
            $mRange = [
                'from_date' => $mStart->toDateString(),
                'to_date' => $mEnd->toDateString(),
            ];
            $mPnl = self::getManagementPnl($mRange);

            $chartLabels[] = $mStart->format('M Y');
            $revenueSeries[] = (float) $mPnl['net_operating_revenue'];
            $expenseSeries[] = (float) $mPnl['operating_expenses'];
        }

        // Overdue Alerts
        $todayStr = $now->toDateString();
        $overdueAlerts = Invoice::with('customer')
            ->whereIn('status', ['issued', 'partially_paid'])
            ->where('due_date', '<', $todayStr)
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Recent Activity
        $recentActivity = DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return [
            'kpis' => [
                'net_revenue' => $pnl['net_operating_revenue'],
                'cash_collected' => $pnl['cash_memo']['cash_collected'],
                'operating_expenses' => $pnl['operating_expenses'],
                'operating_profit' => $pnl['net_operating_profit'],
                'outstanding_receivables' => $outstandingReceivables,
                'overdue_receivables' => $overdueReceivables,
            ],
            'chart' => [
                'labels' => $chartLabels,
                'revenue' => $revenueSeries,
                'expense' => $expenseSeries,
            ],
            'overdue_alerts' => $overdueAlerts,
            'recent_activity' => $recentActivity,
            'currency_symbol' => $settings->currency_symbol,
            'currency_code' => $settings->currency_code,
            'currency_decimals' => $decimals,
        ];
    }
}
