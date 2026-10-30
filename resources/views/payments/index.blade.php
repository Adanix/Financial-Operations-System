<x-layouts.app>
    <x-slot:header>Payments Ledger</x-slot:header>

    <div x-data="{ reverseModal: false, activeId: null, activeAmount: null }" class="space-y-4">
        <!-- Filter & Search Bar -->
        <div class="bg-white border border-[#E3E7E4] p-4 rounded-2xl shadow-xs space-y-3">
            <form action="{{ route('payments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2">
                <div class="relative sm:col-span-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search ref, invoice, customer..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="status" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="reversed" {{ $currentStatus === 'reversed' ? 'selected' : '' }}>Reversed</option>
                </select>

                <select name="method" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    <option value="">All Methods</option>
                    <option value="bank_transfer" {{ $currentMethod === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="card" {{ $currentMethod === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="cash" {{ $currentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="check" {{ $currentMethod === 'check' ? 'selected' : '' }}>Check</option>
                    <option value="other" {{ $currentMethod === 'other' ? 'selected' : '' }}>Other</option>
                </select>

                <div class="flex items-center gap-1.5">
                    <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-[#17211D] rounded-xl transition-colors cursor-pointer w-full">
                        Filter
                    </button>
                    @if($search || $currentStatus || $currentMethod)
                        <a href="{{ route('payments.index') }}" class="px-2 py-2 text-xs text-[#66736C] hover:text-[#17211D]">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Payments List -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <!-- Mobile View: Cards -->
            <div class="divide-y divide-[#E3E7E4] md:hidden">
                @forelse($payments as $pmt)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-sm text-[#17211D] tabular-nums">
                                {{ $currencySymbol }}{{ $pmt->amount }}
                            </div>
                            <a href="{{ route('invoices.show', $pmt->invoice) }}" class="text-xs text-[#0F6B57] hover:underline font-medium">
                                Invoice: {{ $pmt->invoice?->invoice_number }}
                            </a>
                        </div>
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $pmt->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-400 line-through' }}">
                            {{ $pmt->status }}
                        </span>
                    </div>

                    <div class="text-xs text-[#66736C] space-y-0.5">
                        <div>Customer: {{ $pmt->invoice?->customer?->name }}</div>
                        <div>Date: {{ $pmt->payment_date ? $pmt->payment_date->format('M d, Y') : '-' }}</div>
                        <div>Method: {{ ucfirst(str_replace('_', ' ', $pmt->method)) }}</div>
                        @if($pmt->reference) <div>Ref: {{ $pmt->reference }}</div> @endif
                    </div>

                    @if($pmt->status === 'completed' && auth()->user()->isOwner())
                    <div class="pt-2 border-t border-slate-100 flex justify-end">
                        <button type="button" @click="activeId = {{ $pmt->id }}; activeAmount = '{{ $pmt->amount }}'; reverseModal = true;"
                            class="text-xs text-rose-600 font-semibold cursor-pointer">
                            Reverse Payment
                        </button>
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-8 text-center text-xs text-[#66736C]">
                    No payments found.
                </div>
                @endforelse
            </div>

            <!-- Desktop View: Table -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Method</th>
                            <th class="px-5 py-3">Reference</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @forelse($payments as $pmt)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">{{ $pmt->payment_date ? $pmt->payment_date->format('M d, Y') : '-' }}</td>
                            <td class="px-5 py-3.5 font-bold">
                                <a href="{{ route('invoices.show', $pmt->invoice) }}" class="text-[#0F6B57] hover:underline">
                                    {{ $pmt->invoice?->invoice_number }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-[#17211D]">{{ $pmt->invoice?->customer?->name }}</td>
                            <td class="px-5 py-3.5 capitalize">{{ str_replace('_', ' ', $pmt->method) }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $pmt->reference ?: '-' }}</td>
                            <td class="px-5 py-3.5 text-right font-bold tabular-nums text-[#17211D]">
                                {{ $currencySymbol }}{{ $pmt->amount }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $pmt->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-400 line-through' }}">
                                    {{ $pmt->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($pmt->status === 'completed' && auth()->user()->isOwner())
                                    <button type="button" @click="activeId = {{ $pmt->id }}; activeAmount = '{{ $pmt->amount }}'; reverseModal = true;"
                                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                        Reverse
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-[#66736C]">
                                No payments found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-4 border-t border-[#E3E7E4]">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

        <!-- Reverse Payment Modal -->
        <div x-cloak x-show="reverseModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50" @click="reverseModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-[#E3E7E4] max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-rose-700">Reverse Payment</h3>
                    <p class="text-xs text-[#66736C]">Reversing will deduct the payment amount from the invoice, restore the balance due, and record an audit log.</p>

                    <form :action="'/payments/' + activeId + '/reverse'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Reversal Reason <span class="text-rose-500">*</span></label>
                            <textarea name="reversal_reason" rows="3" required placeholder="Mandatory explanation for reversing this payment..."
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="reverseModal = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl cursor-pointer">
                                Confirm Reversal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
