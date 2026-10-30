<x-layouts.app>
    <x-slot:header>{{ $customer->name }}</x-slot:header>

    <div class="space-y-6">
        <!-- Header Profile Card -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-bold text-[#17211D]">{{ $customer->name }}</h1>
                    <span class="inline-flex px-2 py-0.5 text-xs font-bold rounded-full {{ $customer->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                @if($customer->company_name)
                    <div class="text-xs text-[#66736C] font-medium mt-0.5">{{ $customer->company_name }}</div>
                @endif
                <div class="text-xs text-[#66736C] mt-2 flex flex-wrap gap-4">
                    @if($customer->email)
                        <span>Email: <strong class="text-[#17211D]">{{ $customer->email }}</strong></span>
                    @endif
                    @if($customer->phone)
                        <span>Phone: <strong class="text-[#17211D]">{{ $customer->phone }}</strong></span>
                    @endif
                    @if($customer->tax_number)
                        <span>Tax ID: <strong class="text-[#17211D]">{{ $customer->tax_number }}</strong></span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 self-stretch md:self-auto justify-end">
                @if(auth()->user()->isOwner())
                    <a href="{{ route('customers.edit', $customer) }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Edit Profile
                    </a>
                    <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>New Invoice</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 2 Metric Summary Cards: Lifetime Billed vs Outstanding Balance -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-1">Lifetime Invoiced</div>
                <div class="text-2xl font-bold text-[#17211D] tabular-nums">
                    ${{ number_format((float)$lifetimeBilled, 2) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1">Sum of all qualifying issued invoices</div>
            </div>

            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <div class="text-xs font-semibold text-[#66736C] uppercase tracking-wider mb-1">Current Outstanding Balance</div>
                <div class="text-2xl font-bold {{ (float)$outstandingBalance > 0 ? 'text-amber-600' : 'text-[#17211D]' }} tabular-nums">
                    ${{ number_format((float)$outstandingBalance, 2) }}
                </div>
                <div class="text-xs text-[#66736C] mt-1">Unpaid balance across issued and partially paid invoices</div>
            </div>
        </div>

        <!-- Billing Address & Details -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#66736C] mb-2">Billing Address</h3>
            <div class="text-xs text-[#17211D] leading-relaxed">
                {!! nl2br(e($customer->billing_address)) !!}
            </div>
            @if($customer->notes)
                <div class="mt-4 pt-3 border-t border-[#E3E7E4] text-xs text-[#66736C]">
                    <strong class="text-[#17211D]">Notes:</strong> {{ $customer->notes }}
                </div>
            @endif
        </div>

        <!-- Invoices List -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#E3E7E4] flex items-center justify-between">
                <h3 class="text-sm font-bold text-[#17211D]">Invoices for this Customer</h3>
                <span class="text-xs text-[#66736C]">{{ $invoices->total() }} total</span>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Invoice #</th>
                            <th class="px-5 py-3">Issue Date</th>
                            <th class="px-5 py-3">Due Date</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-bold">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-[#0F6B57] hover:underline">
                                    {{ $inv->invoice_number ?: 'Draft #' . $inv->id }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C]">
                                {{ $inv->issue_date ? $inv->issue_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C]">
                                {{ $inv->due_date ? $inv->due_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold tabular-nums">
                                {{ $inv->currency_symbol }}{{ $inv->total }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold tabular-nums {{ (float)$inv->outstanding_balance > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                {{ $inv->currency_symbol }}{{ $inv->outstanding_balance }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
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
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('invoices.show', $inv) }}" class="text-[#0F6B57] font-semibold hover:underline">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-6 text-center text-[#66736C]">
                                No invoices found for this customer.
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
