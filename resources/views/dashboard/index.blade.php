<x-layouts.app>
    <x-slot:header>Financial Dashboard</x-slot:header>

    <div class="space-y-6">

        <!-- Quick Action Shortcuts -->
        @if(auth()->user()->isOwner())
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white border border-[#E3E7E4] p-4 rounded-2xl shadow-xs">
            <div class="text-xs text-[#66736C]">
                Logged in as <strong>{{ auth()->user()->name }}</strong>. You have full management access.
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>New Invoice</span>
                </a>
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-[#17211D] bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Register Expense</span>
                </a>
                <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-[#17211D] bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Add Customer</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 6 Core KPI Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- 1. Net Revenue -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">
                    <span>Net Revenue</span>
                    <span class="text-[10px] font-normal text-slate-500 lowercase">this month</span>
                </div>
                <div class="text-2xl font-bold tracking-tight text-[#17211D] tabular-nums">
                    {{ $currencySymbol }}{{ number_format((float)$kpis['net_revenue'], $currencyDecimals) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1.5">
                    Net sales excluding tax from active issued invoices
                </div>
            </div>

            <!-- 2. Cash Collected -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">
                    <span>Cash Collected</span>
                    <span class="text-[10px] font-normal text-slate-500 lowercase">this month</span>
                </div>
                <div class="text-2xl font-bold tracking-tight text-emerald-700 tabular-nums">
                    {{ $currencySymbol }}{{ number_format((float)$kpis['cash_collected'], $currencyDecimals) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1.5">
                    Completed customer payments received in period
                </div>
            </div>

            <!-- 3. Operating Expenses -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">
                    <span>Operating Expenses</span>
                    <span class="text-[10px] font-normal text-slate-500 lowercase">this month</span>
                </div>
                <div class="text-2xl font-bold tracking-tight text-[#17211D] tabular-nums">
                    {{ $currencySymbol }}{{ number_format((float)$kpis['operating_expenses'], $currencyDecimals) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1.5">
                    P&L basis expenses (net if recoverable, gross if non-rec.)
                </div>
            </div>

            <!-- 4. Operating Profit -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">
                    <span>Operating Profit</span>
                    <span class="text-[10px] font-normal text-slate-500 lowercase">this month</span>
                </div>
                <div class="text-2xl font-bold tracking-tight {{ (float)$kpis['operating_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }} tabular-nums">
                    {{ $currencySymbol }}{{ number_format((float)$kpis['operating_profit'], $currencyDecimals) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1.5">
                    Net Revenue minus Operating Expenses
                </div>
            </div>

            <!-- 5. Outstanding Receivables -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">
                    <span>Outstanding Receivables</span>
                    <span class="text-[10px] font-normal text-slate-500 lowercase">as of today</span>
                </div>
                <div class="text-2xl font-bold tracking-tight text-[#17211D] tabular-nums">
                    {{ $currencySymbol }}{{ number_format((float)$kpis['outstanding_receivables'], $currencyDecimals) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1.5">
                    Total unpaid balance across active issued invoices
                </div>
            </div>

            <!-- 6. Overdue Receivables -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">
                    <span>Overdue Receivables</span>
                    <span class="text-[10px] font-normal text-rose-600 lowercase">attention required</span>
                </div>
                <div class="text-2xl font-bold tracking-tight {{ (float)$kpis['overdue_receivables'] > 0 ? 'text-rose-600' : 'text-[#17211D]' }} tabular-nums">
                    {{ $currencySymbol }}{{ number_format((float)$kpis['overdue_receivables'], $currencyDecimals) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1.5">
                    Overdue subset of outstanding receivables past due date
                </div>
            </div>
        </div>

        <!-- Middle Section: Chart & Overdue Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 6-Month Chart -->
            <div class="lg:col-span-2 bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-[#17211D]">6-Month Revenue vs. Expenses</h2>
                        <p class="text-xs text-[#66736C]">Monthly net operating revenue compared to operating expenses</p>
                    </div>
                </div>
                <div class="relative w-full h-72">
                    <canvas id="revenueExpenseChart"></canvas>
                </div>
            </div>

            <!-- Overdue Invoices Alert Box -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-bold text-[#17211D] flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            Overdue Invoices
                        </h2>
                        <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="text-xs text-[#0F6B57] hover:underline font-semibold">View all</a>
                    </div>
                    <p class="text-xs text-[#66736C] mb-4">Invoices with outstanding balances past their due dates.</p>

                    @if($overdueAlerts->isEmpty())
                        <div class="py-8 text-center text-xs text-[#66736C]">
                            <svg class="w-8 h-8 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>All customer invoices are currently up to date!</div>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($overdueAlerts as $inv)
                            <a href="{{ route('invoices.show', $inv) }}" class="block p-3 rounded-xl border border-rose-100 bg-rose-50/50 hover:bg-rose-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-[#17211D]">{{ $inv->invoice_number }}</span>
                                    <span class="text-xs font-bold text-rose-700 tabular-nums">
                                        {{ $currencySymbol }}{{ $inv->outstanding_balance }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-[#66736C] mt-1 flex justify-between">
                                    <span class="truncate max-w-[150px]">{{ $inv->customer?->name }}</span>
                                    <span class="text-rose-600 font-medium">Due {{ $inv->due_date ? $inv->due_date->format('M d') : '-' }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-[#E3E7E4] mt-4">
                    <a href="{{ route('reports.index', ['tab' => 'aging']) }}" class="text-xs text-[#0F6B57] font-semibold hover:underline flex items-center justify-between">
                        <span>Inspect Aged Receivables Breakdown</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs">
            <h2 class="text-base font-bold text-[#17211D] mb-3">Recent Activity & Audit Trail</h2>
            <div class="divide-y divide-[#E3E7E4]">
                @forelse($recentActivity as $act)
                <div class="py-3 flex items-start justify-between gap-4 text-xs">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-700 shrink-0 mt-0.5 font-bold text-[10px]">
                            {{ strtoupper(substr($act->event, 0, 1)) }}
                        </span>
                        <div>
                            <div class="font-medium text-[#17211D]">{{ $act->description }}</div>
                            <div class="text-[11px] text-[#66736C]">{{ $act->event }}</div>
                        </div>
                    </div>
                    <span class="text-[#66736C] shrink-0 text-[11px] tabular-nums">
                        {{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="py-6 text-center text-xs text-[#66736C]">
                    No activity recorded yet.
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Chart.js Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueExpenseChart');
            if (ctx && window.Chart) {
                new window.Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chart['labels']),
                        datasets: [
                            {
                                label: 'Net Revenue',
                                data: @json($chart['revenue']),
                                backgroundColor: '#0F6B57',
                                borderRadius: 6,
                            },
                            {
                                label: 'Operating Expenses',
                                data: @json($chart['expense']),
                                backgroundColor: '#E2E8F0',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: { size: 11, family: 'sans-serif' }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '{{ $currencySymbol }}' + value.toLocaleString();
                                    },
                                    font: { size: 10 }
                                },
                                grid: { color: '#F1F5F9' }
                            },
                            x: {
                                ticks: { font: { size: 11 } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.app>
