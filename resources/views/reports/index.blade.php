<x-layouts.app>
    <x-slot:header>Financial & Management Reports</x-slot:header>

    <div class="space-y-6">
        <!-- Print Header (Visible only when printing) -->
        <div class="hidden print:block pb-6 mb-6 border-b border-black">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-black">{{ $settings->business_name }}</h1>
                    @if($settings->legal_name && $settings->legal_name !== $settings->business_name)
                        <p class="text-xs text-gray-700">{{ $settings->legal_name }}</p>
                    @endif
                    <p class="text-xs text-gray-700">{{ $settings->address }}</p>
                    @if($settings->tax_number)
                        <p class="text-xs text-gray-700">{{ $settings->tax_label }}: {{ $settings->tax_number }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold uppercase tracking-wider text-black">
                        @if($tab === 'pnl') Management P&L Report
                        @elseif($tab === 'tax') Tax Summary Report
                        @elseif($tab === 'revenue') Revenue & Sales Report
                        @elseif($tab === 'expenses') Expense Breakdown Report
                        @elseif($tab === 'aging') Aged Receivables Report
                        @endif
                    </h2>
                    <p class="text-xs text-gray-700 mt-1">
                        @if($tab === 'aging')
                            As of: {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}
                        @else
                            Period: {{ \Carbon\Carbon::parse($range['from_date'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($range['to_date'])->format('M d, Y') }}
                        @endif
                    </p>
                    <p class="text-[10px] text-gray-500">Printed: {{ now()->timezone($settings->timezone)->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (Hidden in Print) -->
        <div class="print:hidden border-b border-[#E3E7E4]">
            <nav class="flex space-x-6 overflow-x-auto text-xs font-semibold">
                <a href="{{ route('reports.index', ['tab' => 'pnl', 'period' => $preset]) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'pnl' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Management P&L
                </a>
                <a href="{{ route('reports.index', ['tab' => 'tax', 'period' => $preset]) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'tax' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Tax Summary
                </a>
                <a href="{{ route('reports.index', ['tab' => 'revenue', 'period' => $preset]) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'revenue' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Revenue & Sales
                </a>
                <a href="{{ route('reports.index', ['tab' => 'expenses', 'period' => $preset]) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'expenses' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Expense Breakdown
                </a>
                <a href="{{ route('reports.index', ['tab' => 'aging', 'as_of_date' => $asOfDate]) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'aging' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Aged Receivables
                </a>
            </nav>
        </div>

        <!-- Period Toolbar & Export Controls (Hidden in Print) -->
        <div class="print:hidden flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white border border-[#E3E7E4] rounded-2xl p-4 shadow-xs">
            @if($tab !== 'aging')
                <!-- Date Presets -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs font-medium">
                    <span class="text-[#66736C] mr-2">Period:</span>
                    @php
                        $presets = [
                            'this_month' => 'This Month',
                            'last_month' => 'Last Month',
                            'this_quarter' => 'This Quarter',
                            'last_quarter' => 'Last Quarter',
                            'this_year' => 'This Year',
                            'last_year' => 'Last Year',
                        ];
                    @endphp
                    @foreach($presets as $key => $label)
                        <a href="{{ route('reports.index', ['tab' => $tab, 'period' => $key]) }}"
                           class="px-3 py-1.5 rounded-lg transition-colors {{ $preset === $key ? 'bg-[#0F6B57] text-white font-semibold' : 'text-[#66736C] hover:bg-slate-100 hover:text-[#17211D]' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <!-- Custom Range & Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <form action="{{ route('reports.index') }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="hidden" name="period" value="custom">
                        <input type="date" name="from_date" value="{{ $range['from_date'] }}" class="px-2.5 py-1 text-xs rounded-lg border border-[#E3E7E4] bg-white text-[#17211D]">
                        <span class="text-xs text-[#66736C]">to</span>
                        <input type="date" name="to_date" value="{{ $range['to_date'] }}" class="px-2.5 py-1 text-xs rounded-lg border border-[#E3E7E4] bg-white text-[#17211D]">
                        <button type="submit" class="px-3 py-1 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 cursor-pointer">
                            Filter
                        </button>
                    </form>

                    <!-- Export Dropdown -->
                    <div class="relative inline-block text-left" x-data="{ exportOpen: false }">
                        <button @click="exportOpen = !exportOpen" @click.outside="exportOpen = false" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-[#E3E7E4] rounded-xl hover:bg-slate-50 transition-colors cursor-pointer">
                            <span>Export</span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="exportOpen" x-cloak class="absolute right-0 z-20 mt-1 w-44 rounded-xl bg-white border border-[#E3E7E4] shadow-lg py-1 text-xs">
                            <a href="{{ route('reports.export', ['tab' => $tab, 'format' => 'pdf', 'period' => $preset, 'from_date' => $range['from_date'], 'to_date' => $range['to_date']]) }}"
                               class="flex items-center px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#0F6B57] font-medium">
                                Export PDF (.pdf)
                            </a>
                            <a href="{{ route('reports.export', ['tab' => $tab, 'format' => 'xlsx', 'period' => $preset, 'from_date' => $range['from_date'], 'to_date' => $range['to_date']]) }}"
                               class="flex items-center px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#0F6B57] font-medium">
                                Export Excel (.xlsx)
                            </a>
                            <a href="{{ route('reports.export', ['tab' => $tab, 'format' => 'csv', 'period' => $preset, 'from_date' => $range['from_date'], 'to_date' => $range['to_date']]) }}"
                               class="flex items-center px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#0F6B57] font-medium">
                                Export CSV (.csv)
                            </a>
                        </div>
                    </div>

                    <button onclick="window.print()" type="button" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-[#E3E7E4] rounded-xl hover:bg-slate-50 transition-colors cursor-pointer">
                        Print Report
                    </button>
                </div>
            @else
                <!-- Aged Receivables Date Selector -->
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs text-[#66736C]">Receivables As-Of Date:</span>
                    <form action="{{ route('reports.index') }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="aging">
                        <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="px-3 py-1.5 text-xs rounded-xl border border-[#E3E7E4] bg-white text-[#17211D]">
                        <button type="submit" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                            Recalculate
                        </button>
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Export Dropdown -->
                    <div class="relative inline-block text-left" x-data="{ exportOpen: false }">
                        <button @click="exportOpen = !exportOpen" @click.outside="exportOpen = false" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-[#E3E7E4] rounded-xl hover:bg-slate-50 transition-colors cursor-pointer">
                            <span>Export</span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="exportOpen" x-cloak class="absolute right-0 z-20 mt-1 w-44 rounded-xl bg-white border border-[#E3E7E4] shadow-lg py-1 text-xs">
                            <a href="{{ route('reports.export', ['tab' => 'aging', 'format' => 'pdf', 'as_of_date' => $asOfDate]) }}"
                               class="flex items-center px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#0F6B57] font-medium">
                                Export PDF (.pdf)
                            </a>
                            <a href="{{ route('reports.export', ['tab' => 'aging', 'format' => 'xlsx', 'as_of_date' => $asOfDate]) }}"
                               class="flex items-center px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#0F6B57] font-medium">
                                Export Excel (.xlsx)
                            </a>
                            <a href="{{ route('reports.export', ['tab' => 'aging', 'format' => 'csv', 'as_of_date' => $asOfDate]) }}"
                               class="flex items-center px-3.5 py-2 text-slate-700 hover:bg-slate-50 hover:text-[#0F6B57] font-medium">
                                Export CSV (.csv)
                            </a>
                        </div>
                    </div>

                    <button onclick="window.print()" type="button" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-[#E3E7E4] rounded-xl hover:bg-slate-50 transition-colors cursor-pointer">
                        Print Report
                    </button>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: MANAGEMENT P&L                      -->
        <!-- ========================================== -->
        @if($tab === 'pnl')
            <div class="space-y-6">
                <!-- KPI Highlights -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Net Operating Revenue</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['net_operating_revenue'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">Accrual sales ex. sales tax ({{ $reportData['invoices_count'] }} invoices)</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Operating Expenses</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['operating_expenses'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">Ex. recoverable input tax ({{ $reportData['expenses_count'] }} expenses)</span>
                    </div>

                    @php
                        $profit = (float)$reportData['net_operating_profit'];
                    @endphp
                    <div class="bg-white border {{ $profit >= 0 ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold {{ $profit >= 0 ? 'text-emerald-700' : 'text-rose-700' }} uppercase tracking-wider block">
                            Net Operating Profit
                        </span>
                        <div class="text-2xl font-bold {{ $profit >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-2">
                            {{ $currencySymbol }}{{ number_format($profit, 2) }}
                        </div>
                        <span class="text-[11px] {{ $profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1 block">
                            Net Revenue minus Operating Expenses
                        </span>
                    </div>
                </div>

                <!-- Operating Expenses by Category -->
                <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                        <h3 class="text-sm font-bold text-[#17211D]">Operating Expense Breakdown by Category</h3>
                        <p class="text-xs text-[#66736C]">Accrual P&L expense burden itemized by category.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Expense Category</th>
                                    <th class="py-3 px-4 text-right">P&L Amount</th>
                                    <th class="py-3 px-4 text-right">Share of Expenses</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $totalExp = (float)$reportData['operating_expenses'];
                                @endphp
                                @forelse($reportData['category_breakdown'] as $cat => $amt)
                                    @php
                                        $amtFloat = (float)$amt;
                                        $pct = $totalExp > 0 ? round(($amtFloat / $totalExp) * 100, 1) : 0;
                                    @endphp
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 font-semibold text-[#17211D]">{{ $cat }}</td>
                                        <td class="py-3 px-4 text-right font-medium text-slate-800">{{ $currencySymbol }}{{ number_format($amtFloat, 2) }}</td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-600">{{ $pct }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-[#66736C]">No expenses recorded in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="border-t border-[#E3E7E4] bg-slate-50 font-bold text-[#17211D]">
                                <tr>
                                    <td class="py-3 px-4">Total Operating Expenses</td>
                                    <td class="py-3 px-4 text-right">{{ $currencySymbol }}{{ number_format($reportData['operating_expenses'], 2) }}</td>
                                    <td class="py-3 px-4 text-right">100.0%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Cash Flow Memo -->
                <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs">
                    <h3 class="text-sm font-bold text-[#17211D] mb-1">Cash Flow Memo (Non-GAAP Reference)</h3>
                    <p class="text-xs text-[#66736C] mb-4">Actual bank movement dated within this period (payments received vs. cash paid expenses).</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                            <span class="text-xs text-[#66736C] block">Cash Collected from Invoices</span>
                            <span class="text-lg font-bold text-[#17211D] mt-1 block">
                                {{ $currencySymbol }}{{ number_format($reportData['cash_memo']['cash_collected'], 2) }}
                            </span>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                            <span class="text-xs text-[#66736C] block">Cash Paid for Expenses</span>
                            <span class="text-lg font-bold text-[#17211D] mt-1 block">
                                {{ $currencySymbol }}{{ number_format($reportData['cash_memo']['cash_expenses'], 2) }}
                            </span>
                        </div>
                        @php
                            $netCash = (float)$reportData['cash_memo']['net_cash_flow'];
                        @endphp
                        <div class="p-4 rounded-xl border {{ $netCash >= 0 ? 'border-emerald-200 bg-emerald-50/50' : 'border-rose-200 bg-rose-50/50' }}">
                            <span class="text-xs {{ $netCash >= 0 ? 'text-emerald-800' : 'text-rose-800' }} block font-semibold">Net Cash Flow</span>
                            <span class="text-lg font-bold {{ $netCash >= 0 ? 'text-emerald-800' : 'text-rose-800' }} mt-1 block">
                                {{ $currencySymbol }}{{ number_format($netCash, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- TAB 2: TAX SUMMARY                         -->
        <!-- ========================================== -->
        @elseif($tab === 'tax')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Total Output Tax (Collected)</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_output_tax'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">From issued invoices</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Recoverable Input Tax</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_recoverable_input_tax'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">From eligible business expenses</span>
                    </div>

                    @php
                        $netTax = (float)$reportData['estimated_net_tax_position'];
                    @endphp
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Estimated Net Tax Position</span>
                        <div class="text-2xl font-bold {{ $netTax >= 0 ? 'text-[#17211D]' : 'text-blue-700' }} mt-2">
                            {{ $currencySymbol }}{{ number_format(abs($netTax), 2) }}
                        </div>
                        <span class="text-[11px] font-medium {{ $netTax >= 0 ? 'text-amber-700' : 'text-blue-600' }} mt-1 block">
                            {{ $netTax >= 0 ? 'Net Payable to Authority' : 'Net Refund Claimable' }}
                        </span>
                    </div>
                </div>

                <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                        <h3 class="text-sm font-bold text-[#17211D]">Detailed Tax Brackets</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Rate Name</th>
                                    <th class="py-3 px-4 text-right">Taxable Sales</th>
                                    <th class="py-3 px-4 text-right">Output Tax</th>
                                    <th class="py-3 px-4 text-right">Taxable Purchases</th>
                                    <th class="py-3 px-4 text-right">Input Tax</th>
                                    <th class="py-3 px-4 text-right">Net Position</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData['rates'] as $row)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 font-semibold text-[#17211D]">{{ $row['name'] }} ({{ (float)$row['rate'] }}%)</td>
                                        <td class="py-3 px-4 text-right text-slate-700">{{ $currencySymbol }}{{ number_format($row['taxable_sales'], 2) }}</td>
                                        <td class="py-3 px-4 text-right font-semibold text-[#17211D]">{{ $currencySymbol }}{{ number_format($row['output_tax'], 2) }}</td>
                                        <td class="py-3 px-4 text-right text-slate-700">{{ $currencySymbol }}{{ number_format($row['taxable_purchases'], 2) }}</td>
                                        <td class="py-3 px-4 text-right font-semibold text-[#17211D]">{{ $currencySymbol }}{{ number_format($row['recoverable_input_tax'], 2) }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-[#17211D]">{{ $currencySymbol }}{{ number_format($row['net_position'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-[#66736C]">No tax activity in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- TAB 3: REVENUE & SALES                     -->
        <!-- ========================================== -->
        @elseif($tab === 'revenue')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Net Sales</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_net_sales'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">Excluding sales tax</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Total Invoiced</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_invoiced'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">Gross invoice value</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Cash Received</span>
                        <div class="text-2xl font-bold text-emerald-700 mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_paid'], 2) }}
                        </div>
                        <span class="text-[11px] text-emerald-600 mt-1 block">Payments on these invoices</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Unpaid Balance</span>
                        <div class="text-2xl font-bold text-amber-700 mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_outstanding'], 2) }}
                        </div>
                        <span class="text-[11px] text-amber-600 mt-1 block">Remaining receivables</span>
                    </div>
                </div>

                <!-- Top Customers -->
                <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                        <h3 class="text-sm font-bold text-[#17211D]">Top Customers by Net Sales</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4 text-center">Invoices</th>
                                    <th class="py-3 px-4 text-right">Net Sales</th>
                                    <th class="py-3 px-4 text-right">Total Invoiced</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData['top_customers'] as $c)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 font-semibold text-[#17211D]">
                                            <a href="{{ route('customers.show', $c['id']) }}" class="text-[#0F6B57] hover:underline">
                                                {{ $c['name'] }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-center font-mono">{{ $c['invoice_count'] }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-[#17211D]">{{ $currencySymbol }}{{ number_format($c['net_sales'], 2) }}</td>
                                        <td class="py-3 px-4 text-right font-medium text-slate-700">{{ $currencySymbol }}{{ number_format($c['total_invoiced'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-[#66736C]">No sales recorded in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- TAB 4: EXPENSE BREAKDOWN                   -->
        <!-- ========================================== -->
        @elseif($tab === 'expenses')
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Net Operating Expenses (P&L)</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_pnl_expenses'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">Excluding recoverable input tax</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Gross Expenses (Invoice Total)</span>
                        <div class="text-2xl font-bold text-[#17211D] mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_gross_expenses'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">All purchase amounts</span>
                    </div>

                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                        <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Cash Paid Expenses</span>
                        <div class="text-2xl font-bold text-slate-800 mt-2">
                            {{ $currencySymbol }}{{ number_format($reportData['total_paid_cash'], 2) }}
                        </div>
                        <span class="text-[11px] text-[#66736C] mt-1 block">Settled with bank/card</span>
                    </div>
                </div>

                <!-- Category Breakdown Table -->
                <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                        <h3 class="text-sm font-bold text-[#17211D]">Expenses by Category</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Category</th>
                                    <th class="py-3 px-4 text-center">Transactions</th>
                                    <th class="py-3 px-4 text-right">P&L Amount</th>
                                    <th class="py-3 px-4 text-right">Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData['category_breakdown'] as $c)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 font-semibold text-[#17211D]">{{ $c['category'] }}</td>
                                        <td class="py-3 px-4 text-center font-mono">{{ $c['count'] }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-[#17211D]">{{ $currencySymbol }}{{ number_format($c['amount'], 2) }}</td>
                                        <td class="py-3 px-4 text-right font-mono">{{ $c['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-[#66736C]">No expenses found for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- TAB 5: AGED RECEIVABLES                    -->
        <!-- ========================================== -->
        @elseif($tab === 'aging')
            <div class="space-y-6">
                <!-- Aging Buckets -->
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-4 shadow-xs col-span-2 sm:col-span-1">
                        <span class="text-[11px] font-semibold text-[#66736C] uppercase tracking-wider block">Total Outstanding</span>
                        <div class="text-xl font-bold text-[#17211D] mt-1">
                            {{ $currencySymbol }}{{ number_format($reportData['total_receivables'], 2) }}
                        </div>
                    </div>

                    <div class="bg-white border border-emerald-200 bg-emerald-50/20 rounded-2xl p-4 shadow-xs">
                        <span class="text-[11px] font-semibold text-emerald-800 uppercase tracking-wider block">Current</span>
                        <div class="text-lg font-bold text-emerald-800 mt-1">
                            {{ $currencySymbol }}{{ number_format($reportData['buckets']['current'], 2) }}
                        </div>
                        <span class="text-[10px] text-emerald-600">Not yet overdue</span>
                    </div>

                    <div class="bg-white border border-amber-200 bg-amber-50/20 rounded-2xl p-4 shadow-xs">
                        <span class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider block">1 - 30 Days</span>
                        <div class="text-lg font-bold text-amber-800 mt-1">
                            {{ $currencySymbol }}{{ number_format($reportData['buckets']['1_30'], 2) }}
                        </div>
                    </div>

                    <div class="bg-white border border-amber-300 bg-amber-50/30 rounded-2xl p-4 shadow-xs">
                        <span class="text-[11px] font-semibold text-amber-900 uppercase tracking-wider block">31 - 60 Days</span>
                        <div class="text-lg font-bold text-amber-900 mt-1">
                            {{ $currencySymbol }}{{ number_format($reportData['buckets']['31_60'], 2) }}
                        </div>
                    </div>

                    <div class="bg-white border border-rose-200 bg-rose-50/20 rounded-2xl p-4 shadow-xs">
                        <span class="text-[11px] font-semibold text-rose-800 uppercase tracking-wider block">61 - 90 Days</span>
                        <div class="text-lg font-bold text-rose-800 mt-1">
                            {{ $currencySymbol }}{{ number_format($reportData['buckets']['61_90'], 2) }}
                        </div>
                    </div>

                    <div class="bg-white border border-rose-300 bg-rose-50/40 rounded-2xl p-4 shadow-xs">
                        <span class="text-[11px] font-semibold text-rose-900 uppercase tracking-wider block">90+ Days</span>
                        <div class="text-lg font-bold text-rose-900 mt-1">
                            {{ $currencySymbol }}{{ number_format($reportData['buckets']['90_plus'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Itemized Invoices Table -->
                <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                        <h3 class="text-sm font-bold text-[#17211D]">Aged Invoices as of {{ $reportData['as_of_label'] }}</h3>
                        <p class="text-xs text-[#66736C]">Historical balance reconstruction based on payment completion dates.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Invoice #</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Issue Date</th>
                                    <th class="py-3 px-4">Due Date</th>
                                    <th class="py-3 px-4 text-right">Total</th>
                                    <th class="py-3 px-4 text-right">Balance Due</th>
                                    <th class="py-3 px-4 text-center">Days Overdue</th>
                                    <th class="py-3 px-4 text-center">Aging Bracket</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($reportData['invoices'] as $row)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 font-mono font-semibold">
                                            <a href="{{ route('invoices.show', $row['invoice']->id) }}" class="text-[#0F6B57] hover:underline">
                                                {{ $row['invoice_number'] }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 font-medium text-[#17211D]">{{ $row['customer_name'] }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $row['issue_date'] }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $row['due_date'] }}</td>
                                        <td class="py-3 px-4 text-right text-slate-700">{{ $currencySymbol }}{{ number_format($row['total'], 2) }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-[#17211D]">{{ $currencySymbol }}{{ number_format($row['balance'], 2) }}</td>
                                        <td class="py-3 px-4 text-center font-mono">
                                            @if($row['days_overdue'] > 0)
                                                <span class="text-rose-600 font-semibold">{{ $row['days_overdue'] }}d</span>
                                            @else
                                                <span class="text-emerald-600">Current</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @php
                                                $b = $row['bucket'];
                                                $badgeClasses = match($b) {
                                                    'current' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    '1_30' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    '31_60' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                    '61_90' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                    '90_plus' => 'bg-rose-100 text-rose-800 border-rose-300',
                                                };
                                                $badgeLabel = match($b) {
                                                    'current' => 'Current',
                                                    '1_30' => '1-30 Days',
                                                    '31_60' => '31-60 Days',
                                                    '61_90' => '61-90 Days',
                                                    '90_plus' => '90+ Days',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeClasses }}">
                                                {{ $badgeLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-[#66736C]">
                                            No outstanding receivables found as of {{ $reportData['as_of_label'] }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
