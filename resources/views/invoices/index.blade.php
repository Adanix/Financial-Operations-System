<x-layouts.app>
    <x-slot:header>Invoices</x-slot:header>

    <div class="space-y-4">
        <!-- Filter & Search Bar -->
        <div class="bg-white border border-[#E3E7E4] p-4 rounded-2xl shadow-xs space-y-3">
            <form action="{{ route('invoices.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search invoice #, customer..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="status" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="issued" {{ $currentStatus === 'issued' ? 'selected' : '' }}>Issued (Unpaid)</option>
                        <option value="partially_paid" {{ $currentStatus === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ $currentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ $currentStatus === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="void" {{ $currentStatus === 'void' ? 'selected' : '' }}>Void</option>
                    </select>

                    <select name="customer_id" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (string)$currentCustomer === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-1.5">
                        <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-[#17211D] rounded-xl transition-colors cursor-pointer w-full">
                            Filter
                        </button>
                        @if($search || $currentStatus || $currentCustomer)
                            <a href="{{ route('invoices.index') }}" class="px-2 py-2 text-xs text-[#66736C] hover:text-[#17211D]">Reset</a>
                        @endif
                    </div>
                </div>

                @if(auth()->user()->isOwner())
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>New Invoice</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Invoices List Container -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <!-- Mobile Cards -->
            <div class="divide-y divide-[#E3E7E4] md:hidden">
                @forelse($invoices as $inv)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('invoices.show', $inv) }}" class="font-bold text-sm text-[#0F6B57] hover:underline">
                                {{ $inv->invoice_number ?: 'Draft #' . $inv->id }}
                            </a>
                            <div class="text-xs text-[#17211D] font-medium">{{ $inv->customer?->name }}</div>
                        </div>
                        @php $st = $inv->display_status; @endphp
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded-full
                            {{ $st === 'paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                            {{ $st === 'partially_paid' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                            {{ $st === 'issued' ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' : '' }}
                            {{ $st === 'overdue' ? 'bg-rose-50 text-rose-800 border border-rose-200' : '' }}
                            {{ $st === 'draft' ? 'bg-slate-100 text-slate-700' : '' }}
                            {{ $st === 'void' ? 'bg-slate-100 text-slate-400 line-through' : '' }}
                        ">
                            {{ $st }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs text-[#66736C]">
                        <span>Due: {{ $inv->due_date ? $inv->due_date->format('M d, Y') : '-' }}</span>
                        <div class="text-right">
                            <span class="font-bold text-sm text-[#17211D] tabular-nums">{{ $currencySymbol }}{{ $inv->total }}</span>
                            @if((float)$inv->outstanding_balance > 0 && $st !== 'draft')
                                <div class="text-[11px] font-semibold text-amber-700">Due: {{ $currencySymbol }}{{ $inv->outstanding_balance }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 text-xs border-t border-[#F0F2F0]">
                        <a href="{{ route('invoices.show', $inv) }}" class="text-[#0F6B57] font-semibold hover:underline">View</a>
                        <a href="{{ route('invoices.pdf', $inv) }}" class="text-slate-600 hover:text-slate-900">PDF</a>
                        @if($inv->status === 'draft' && auth()->user()->isOwner())
                            <a href="{{ route('invoices.edit', $inv) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs text-[#66736C]">
                    No invoices found matching your criteria.
                </div>
                @endforelse
            </div>

            <!-- Desktop & Tablet Table -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Invoice #</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Issue Date</th>
                            <th class="px-5 py-3">Due Date</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Balance Due</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-bold">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-[#0F6B57] hover:underline">
                                    {{ $inv->invoice_number ?: 'Draft #' . $inv->id }}
                                </a>
                                @if($inv->reference)
                                    <div class="text-[10px] text-[#66736C] font-normal">{{ $inv->reference }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-medium text-[#17211D]">
                                {{ $inv->customer?->name }}
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C]">
                                {{ $inv->issue_date ? $inv->issue_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C]">
                                {{ $inv->due_date ? $inv->due_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-[#17211D] tabular-nums">
                                {{ $currencySymbol }}{{ $inv->total }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold tabular-nums {{ (float)$inv->outstanding_balance > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ $currencySymbol }}{{ $inv->outstanding_balance }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php $st = $inv->display_status; @endphp
                                <span class="inline-flex px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full
                                    {{ $st === 'paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                                    {{ $st === 'partially_paid' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                                    {{ $st === 'issued' ? 'bg-indigo-50 text-indigo-800 border border-indigo-200' : '' }}
                                    {{ $st === 'overdue' ? 'bg-rose-50 text-rose-800 border border-rose-200' : '' }}
                                    {{ $st === 'draft' ? 'bg-slate-100 text-slate-700' : '' }}
                                    {{ $st === 'void' ? 'bg-slate-100 text-slate-400 line-through' : '' }}
                                ">
                                    {{ $st }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-[#0F6B57] font-semibold hover:underline">View</a>
                                <a href="{{ route('invoices.pdf', $inv) }}" class="text-slate-600 hover:text-slate-900">PDF</a>
                                @if($inv->status === 'draft' && auth()->user()->isOwner())
                                    <a href="{{ route('invoices.edit', $inv) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-[#66736C]">
                                No invoices found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="p-4 border-t border-[#E3E7E4]">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
