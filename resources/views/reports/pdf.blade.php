<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        @if($tab === 'pnl') Management Profit & Loss Report
        @elseif($tab === 'tax') Tax Summary Report
        @elseif($tab === 'revenue') Revenue & Sales Ledger
        @elseif($tab === 'expenses') Expense Breakdown Ledger
        @elseif($tab === 'aging') Aged Receivables Ledger
        @endif
    </title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #17211D;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0F6B57;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0F6B57;
            margin-bottom: 3px;
        }
        .company-legal {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }
        .company-details {
            font-size: 9px;
            color: #66736C;
            line-height: 1.3;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
            text-transform: uppercase;
            color: #17211D;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 9px;
            color: #66736C;
            text-align: right;
            line-height: 1.4;
        }
        .kpi-cards {
            width: 100%;
            margin-bottom: 18px;
        }
        .kpi-cards table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-card {
            background-color: #F8FAF9;
            border: 1px solid #E3E7E4;
            padding: 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #66736C;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: bold;
            color: #17211D;
        }
        .kpi-sub {
            font-size: 8px;
            color: #88928D;
            margin-top: 2px;
        }
        .section-heading {
            font-size: 11px;
            font-weight: bold;
            color: #17211D;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 14px;
            margin-bottom: 6px;
            border-bottom: 1px solid #E3E7E4;
            padding-bottom: 3px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #F1F4F2;
            color: #4A5650;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border-top: 1px solid #D1D8D4;
            border-bottom: 1px solid #D1D8D4;
            text-align: left;
        }
        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #EEF1F0;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) td {
            background-color: #FCFDFD;
        }
        .data-table tfoot td {
            font-weight: bold;
            background-color: #F1F4F2;
            border-top: 1px solid #D1D8D4;
            border-bottom: 1px solid #D1D8D4;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #88928D;
            border-top: 1px solid #E3E7E4;
            padding-top: 6px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td style="vertical-align: top; width: 55%;">
                    <div class="company-name">{{ $settings->business_name }}</div>
                    @if($settings->legal_name && $settings->legal_name !== $settings->business_name)
                        <div class="company-legal">{{ $settings->legal_name }}</div>
                    @endif
                    <div class="company-details">
                        {{ $settings->address }}<br>
                        Email: {{ $settings->email }} &bull; Currency: {{ $settings->currency_code }} ({{ $settings->currency_symbol }})<br>
                        @if($settings->tax_number)
                            {{ $settings->tax_label }}: {{ $settings->tax_number }}
                        @endif
                    </div>
                </td>
                <td style="vertical-align: top; width: 45%; text-align: right;">
                    <div class="report-title">
                        @if($tab === 'pnl') Management Profit & Loss
                        @elseif($tab === 'tax') Tax Summary Report
                        @elseif($tab === 'revenue') Revenue & Sales Ledger
                        @elseif($tab === 'expenses') Expense Breakdown Ledger
                        @elseif($tab === 'aging') Aged Receivables Ledger
                        @endif
                    </div>
                    <div class="report-meta">
                        @if($tab === 'aging')
                            As-of Date: <strong>{{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</strong>
                        @else
                            Period: <strong>{{ \Carbon\Carbon::parse($range['from_date'])->format('M d, Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($range['to_date'])->format('M d, Y') }}</strong>
                        @endif
                        <br>
                        Generated: {{ $printedAt }}<br>
                        Confidential &bull; Single-Entity Management Report
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: MANAGEMENT P&L                      -->
    <!-- ========================================== -->
    @if($tab === 'pnl')
        <div class="kpi-cards">
            <table>
                <tr>
                    <td class="kpi-card" style="width: 33%;">
                        <div class="kpi-label">Net Operating Revenue</div>
                        <div class="kpi-value" style="color: #0F6B57;">
                            {{ $currencySymbol }}{{ number_format($reportData['net_operating_revenue'], 2) }}
                        </div>
                        <div class="kpi-sub">{{ $reportData['invoices_count'] }} issued invoices (ex. tax)</div>
                    </td>
                    <td class="kpi-card" style="width: 33%;">
                        <div class="kpi-label">Operating Expenses</div>
                        <div class="kpi-value" style="color: #4A5650;">
                            {{ $currencySymbol }}{{ number_format($reportData['operating_expenses'], 2) }}
                        </div>
                        <div class="kpi-sub">{{ $reportData['expenses_count'] }} expenses (ex. input tax)</div>
                    </td>
                    @php $profit = (float)$reportData['net_operating_profit']; @endphp
                    <td class="kpi-card" style="width: 34%; {{ $profit >= 0 ? 'background-color: #ECFDF5;' : 'background-color: #FEF2F2;' }}">
                        <div class="kpi-label" style="{{ $profit >= 0 ? 'color: #065F46;' : 'color: #991B1B;' }}">Net Operating Profit</div>
                        <div class="kpi-value" style="{{ $profit >= 0 ? 'color: #065F46;' : 'color: #991B1B;' }}">
                            {{ $currencySymbol }}{{ number_format($profit, 2) }}
                        </div>
                        <div class="kpi-sub">Net Revenue minus Expenses</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-heading">Operating Expense Breakdown by Category</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">P&L Amount</th>
                    <th class="text-right">Share of Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalExp = (float)$reportData['operating_expenses']; @endphp
                @forelse($reportData['category_breakdown'] as $cat => $amt)
                    @php
                        $amtFloat = (float)$amt;
                        $pct = $totalExp > 0 ? round(($amtFloat / $totalExp) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="font-bold">{{ $cat }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($amtFloat, 2) }}</td>
                        <td class="text-right">{{ $pct }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No expenses recorded for this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Operating Expenses</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['operating_expenses'], 2) }}</td>
                    <td class="text-right">100.0%</td>
                </tr>
            </tfoot>
        </table>

        <div class="section-heading">Cash Flow Memo (Non-GAAP Reference)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cash Flow Movement</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cash Collections from Invoices (Received in Period)</td>
                    <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($reportData['cash_memo']['cash_collected'], 2) }}</td>
                </tr>
                <tr>
                    <td>Cash Disbursements for Expenses (Paid in Period)</td>
                    <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($reportData['cash_memo']['cash_expenses'], 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>Net Operating Cash Flow</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['cash_memo']['net_cash_flow'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

    <!-- ========================================== -->
    <!-- TAB 2: TAX SUMMARY                         -->
    <!-- ========================================== -->
    @elseif($tab === 'tax')
        <div class="kpi-cards">
            <table>
                <tr>
                    <td class="kpi-card" style="width: 33%;">
                        <div class="kpi-label">Output Tax (Collected)</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_output_tax'], 2) }}</div>
                        <div class="kpi-sub">From issued & paid invoices</div>
                    </td>
                    <td class="kpi-card" style="width: 33%;">
                        <div class="kpi-label">Recoverable Input Tax</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_recoverable_input_tax'], 2) }}</div>
                        <div class="kpi-sub">Eligible business expenses</div>
                    </td>
                    @php $netTax = (float)$reportData['estimated_net_tax_position']; @endphp
                    <td class="kpi-card" style="width: 34%;">
                        <div class="kpi-label">Estimated Net Tax Position</div>
                        <div class="kpi-value" style="{{ $netTax >= 0 ? 'color: #17211D;' : 'color: #1D4ED8;' }}">
                            {{ $currencySymbol }}{{ number_format(abs($netTax), 2) }}
                        </div>
                        <div class="kpi-sub">{{ $netTax >= 0 ? 'Payable to Tax Authority' : 'Net Refund Claimable' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-heading">Tax Brackets Breakdown</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tax Bracket</th>
                    <th class="text-right">Taxable Sales</th>
                    <th class="text-right">Output Tax</th>
                    <th class="text-right">Taxable Purchases</th>
                    <th class="text-right">Input Tax</th>
                    <th class="text-right">Net Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['rates'] as $r)
                    <tr>
                        <td class="font-bold">{{ $r['name'] }} ({{ (float)$r['rate'] }}%)</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($r['taxable_sales'], 2) }}</td>
                        <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($r['output_tax'], 2) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($r['taxable_purchases'], 2) }}</td>
                        <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($r['recoverable_input_tax'], 2) }}</td>
                        <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($r['net_position'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No tax transactions in this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_output_tax'], 2) }}</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_recoverable_input_tax'], 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['estimated_net_tax_position'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

    <!-- ========================================== -->
    <!-- TAB 3: REVENUE & SALES                     -->
    <!-- ========================================== -->
    @elseif($tab === 'revenue')
        <div class="kpi-cards">
            <table>
                <tr>
                    <td class="kpi-card" style="width: 25%;">
                        <div class="kpi-label">Net Sales</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_net_sales'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 25%;">
                        <div class="kpi-label">Total Invoiced</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_invoiced'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 25%;">
                        <div class="kpi-label">Cash Collected</div>
                        <div class="kpi-value" style="color: #065F46;">{{ $currencySymbol }}{{ number_format($reportData['total_paid'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 25%;">
                        <div class="kpi-label">Outstanding Balance</div>
                        <div class="kpi-value" style="color: #B45309;">{{ $currencySymbol }}{{ number_format($reportData['total_outstanding'], 2) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-heading">Issued Invoices Ledger</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['invoices'] as $inv)
                    <tr>
                        <td class="font-bold">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->customer?->name }}</td>
                        <td>{{ $inv->issue_date->format('Y-m-d') }}</td>
                        <td>{{ $inv->due_date->format('Y-m-d') }}</td>
                        <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $inv->status) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($inv->total, 2) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($inv->tax_total, 2) }}</td>
                        <td class="text-right" style="color: #065F46;">{{ $currencySymbol }}{{ number_format($inv->amount_paid, 2) }}</td>
                        <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($inv->outstanding_balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No invoices issued in this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">Total</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_invoiced'], 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_tax'], 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_paid'], 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_outstanding'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

    <!-- ========================================== -->
    <!-- TAB 4: EXPENSES                            -->
    <!-- ========================================== -->
    @elseif($tab === 'expenses')
        <div class="kpi-cards">
            <table>
                <tr>
                    <td class="kpi-card" style="width: 33%;">
                        <div class="kpi-label">P&L Operating Expenses</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_pnl_expenses'], 2) }}</div>
                        <div class="kpi-sub">Ex. recoverable input tax</div>
                    </td>
                    <td class="kpi-card" style="width: 33%;">
                        <div class="kpi-label">Gross Expense Purchases</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_gross_expenses'], 2) }}</div>
                        <div class="kpi-sub">Total invoice face value</div>
                    </td>
                    <td class="kpi-card" style="width: 34%;">
                        <div class="kpi-label">Cash Settled Expenses</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_paid_cash'], 2) }}</div>
                        <div class="kpi-sub">Paid via bank/card/cash</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-heading">Operating Expenses Ledger</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-right">Net</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Gross</th>
                    <th class="text-center">Recoverable</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['expenses'] as $e)
                    <tr>
                        <td>{{ $e->expense_date->format('Y-m-d') }}</td>
                        <td class="font-bold">{{ $e->vendor }}</td>
                        <td>{{ $e->category?->name }}</td>
                        <td>{{ $e->description }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($e->net_amount, 2) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($e->tax_amount, 2) }}</td>
                        <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($e->gross_amount, 2) }}</td>
                        <td class="text-center">{{ $e->tax_recoverable ? 'Yes' : 'No' }}</td>
                        <td class="text-center" style="text-transform: capitalize;">{{ $e->payment_status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No expenses recorded in this period.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Total</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_gross_expenses'], 2) }}</td>
                    <td colspan="2">-</td>
                </tr>
            </tfoot>
        </table>

    <!-- ========================================== -->
    <!-- TAB 5: AGED RECEIVABLES                    -->
    <!-- ========================================== -->
    @elseif($tab === 'aging')
        <div class="kpi-cards">
            <table>
                <tr>
                    <td class="kpi-card" style="width: 16%;">
                        <div class="kpi-label">Total Due</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['total_receivables'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 16%; background-color: #ECFDF5;">
                        <div class="kpi-label" style="color: #065F46;">Current</div>
                        <div class="kpi-value" style="color: #065F46;">{{ $currencySymbol }}{{ number_format($reportData['buckets']['current'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 17%;">
                        <div class="kpi-label">1 - 30 Days</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['buckets']['1_30'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 17%;">
                        <div class="kpi-label">31 - 60 Days</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['buckets']['31_60'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 17%;">
                        <div class="kpi-label">61 - 90 Days</div>
                        <div class="kpi-value">{{ $currencySymbol }}{{ number_format($reportData['buckets']['61_90'], 2) }}</div>
                    </td>
                    <td class="kpi-card" style="width: 17%; background-color: #FEF2F2;">
                        <div class="kpi-label" style="color: #991B1B;">90+ Days</div>
                        <div class="kpi-value" style="color: #991B1B;">{{ $currencySymbol }}{{ number_format($reportData['buckets']['90_plus'], 2) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-heading">Aged Invoices as of {{ $reportData['as_of_label'] }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Paid as of Date</th>
                    <th class="text-right">Balance Due</th>
                    <th class="text-center">Days Overdue</th>
                    <th class="text-center">Bracket</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData['invoices'] as $row)
                    <tr>
                        <td class="font-bold">{{ $row['invoice_number'] }}</td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td>{{ $row['issue_date'] }}</td>
                        <td>{{ $row['due_date'] }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($row['total'], 2) }}</td>
                        <td class="text-right">{{ $currencySymbol }}{{ number_format($row['effective_paid'], 2) }}</td>
                        <td class="text-right font-bold">{{ $currencySymbol }}{{ number_format($row['balance'], 2) }}</td>
                        <td class="text-center">{{ $row['days_overdue'] > 0 ? $row['days_overdue'] . 'd' : 'Current' }}</td>
                        <td class="text-center font-bold" style="text-transform: uppercase;">{{ str_replace('_', '-', $row['bucket']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No outstanding receivables found as of {{ $reportData['as_of_label'] }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">Total Outstanding Balance</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($reportData['total_receivables'], 2) }}</td>
                    <td colspan="2">-</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        <div>SoloLedger Financial Management &bull; Generated from live database</div>
        <div>Page printed on {{ $printedAt }}</div>
    </div>
</body>
</html>
