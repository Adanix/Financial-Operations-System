<x-layouts.app>
    <x-slot:header>Expense #{{ $expense->id }} &bull; {{ $expense->vendor }}</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs / Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-[#66736C]">
                <a href="{{ route('expenses.index') }}" class="hover:text-[#17211D]">Expenses</a>
                <span>/</span>
                <span class="text-[#17211D] font-semibold">#{{ $expense->id }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('expenses.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-[#E3E7E4] rounded-xl hover:bg-slate-50 transition-colors">
                    Back to Expenses
                </a>

                @if(auth()->user()->isOwner())
                    <a href="{{ route('expenses.edit', $expense) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-[#E3E7E4] rounded-xl hover:bg-slate-50 transition-colors">
                        Edit
                    </a>
                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense record? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-colors cursor-pointer">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
            <!-- Header bar -->
            <div class="p-6 border-b border-[#E3E7E4] flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-[#17211D]">{{ $expense->vendor }}</h1>
                        @if($expense->payment_status === 'paid')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Paid
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                Unpaid
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-[#66736C] mt-1">
                        Category: <span class="font-medium text-[#17211D]">{{ $expense->category?->name ?? 'Uncategorized' }}</span> &bull; 
                        Date: <span class="font-medium text-[#17211D]">{{ $expense->expense_date->format('M d, Y') }}</span>
                    </p>
                </div>

                <div class="text-right">
                    <span class="text-xs text-[#66736C] uppercase tracking-wider block">Total Amount</span>
                    <span class="text-2xl font-bold text-[#17211D]">{{ $settings->currency_symbol }}{{ number_format($expense->gross_amount, $settings->currency_decimals) }}</span>
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="p-6 space-y-6">
                <!-- Description -->
                <div>
                    <h3 class="text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-2">Description</h3>
                    <p class="text-sm text-[#17211D] whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100">{{ $expense->description }}</p>
                </div>

                <!-- Financial Breakdown -->
                <div>
                    <h3 class="text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-3">Financial & Tax Breakdown</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="p-4 rounded-xl border border-[#E3E7E4] bg-white">
                            <span class="text-xs text-[#66736C] block">Entered Amount</span>
                            <span class="text-base font-bold text-[#17211D] mt-1 block">
                                {{ $settings->currency_symbol }}{{ number_format($expense->entered_amount, $settings->currency_decimals) }}
                            </span>
                            <span class="text-[11px] text-[#66736C] capitalize">{{ $expense->pricing_mode }} Tax</span>
                        </div>

                        <div class="p-4 rounded-xl border border-[#E3E7E4] bg-white">
                            <span class="text-xs text-[#66736C] block">Net Expense</span>
                            <span class="text-base font-bold text-[#17211D] mt-1 block">
                                {{ $settings->currency_symbol }}{{ number_format($expense->net_amount, $settings->currency_decimals) }}
                            </span>
                            <span class="text-[11px] text-[#66736C]">Excluding Tax</span>
                        </div>

                        <div class="p-4 rounded-xl border border-[#E3E7E4] bg-white">
                            <span class="text-xs text-[#66736C] block">Tax Amount</span>
                            <span class="text-base font-bold text-[#17211D] mt-1 block">
                                {{ $settings->currency_symbol }}{{ number_format($expense->tax_amount, $settings->currency_decimals) }}
                            </span>
                            <span class="text-[11px] text-[#66736C]">
                                {{ $expense->tax_name ? $expense->tax_name . ' (' . (float)$expense->tax_rate . '%)' : 'Zero Tax' }}
                            </span>
                        </div>

                        <div class="p-4 rounded-xl border border-[#E3E7E4] bg-white">
                            <span class="text-xs text-[#66736C] block">Tax Treatment</span>
                            <div class="mt-2">
                                @if($expense->tax_recoverable)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Recoverable Input Tax
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        Non-Recoverable (Cost)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment & Filing Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-[#E3E7E4]">
                    <div>
                        <h3 class="text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-3">Payment Information</h3>
                        <dl class="space-y-2 text-xs">
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <dt class="text-[#66736C]">Status:</dt>
                                <dd class="font-medium text-[#17211D] capitalize">{{ $expense->payment_status }}</dd>
                            </div>
                            @if($expense->payment_status === 'paid')
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <dt class="text-[#66736C]">Payment Date:</dt>
                                    <dd class="font-medium text-[#17211D]">{{ optional($expense->payment_date)->format('M d, Y') ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-100">
                                    <dt class="text-[#66736C]">Payment Method:</dt>
                                    <dd class="font-medium text-[#17211D] capitalize">{{ str_replace('_', ' ', $expense->payment_method ?? 'N/A') }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between py-1 border-b border-slate-100">
                                <dt class="text-[#66736C]">Invoice / Receipt Ref:</dt>
                                <dd class="font-mono font-medium text-[#17211D]">{{ $expense->reference ?: 'None' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-3">Receipt Document</h3>
                        @if($expense->receipt_path)
                            <div class="p-4 rounded-xl border border-[#E3E7E4] bg-slate-50 flex items-center justify-between">
                                <div class="truncate mr-3">
                                    <p class="text-xs font-medium text-[#17211D] truncate">{{ $expense->receipt_original_name ?: 'receipt_file' }}</p>
                                    <p class="text-[11px] text-[#66736C]">Stored on private storage</p>
                                </div>
                                <a href="{{ route('expenses.receipt', $expense) }}" class="px-3 py-1.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-lg transition-colors whitespace-nowrap">
                                    Download Receipt
                                </a>
                            </div>
                        @else
                            <div class="p-4 rounded-xl border border-dashed border-[#E3E7E4] text-center text-xs text-[#66736C]">
                                No receipt file attached to this expense.
                            </div>
                        @endif

                        @if($expense->notes)
                            <div class="mt-4">
                                <h4 class="text-xs font-semibold text-[#66736C] mb-1">Internal Notes</h4>
                                <p class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg">{{ $expense->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
