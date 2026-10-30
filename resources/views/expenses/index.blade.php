<x-layouts.app>
    <x-slot:header>Expenses</x-slot:header>

    <div class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white border border-[#E3E7E4] p-4 rounded-2xl shadow-xs space-y-3">
            <form action="{{ route('expenses.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search vendor, description..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="category_id" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)$currentCategory === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select name="payment_status" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        <option value="">All Payment States</option>
                        <option value="paid" {{ $currentPaymentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ $currentPaymentStatus === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>

                    <div class="flex items-center gap-1.5">
                        <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-[#17211D] rounded-xl transition-colors cursor-pointer w-full">
                            Filter
                        </button>
                        @if($search || $currentCategory || $currentPaymentStatus)
                            <a href="{{ route('expenses.index') }}" class="px-2 py-2 text-xs text-[#66736C] hover:text-[#17211D]">Reset</a>
                        @endif
                    </div>
                </div>

                @if(auth()->user()->isOwner())
                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Register Expense</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Expenses List Container -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <!-- Mobile Cards -->
            <div class="divide-y divide-[#E3E7E4] md:hidden">
                @forelse($expenses as $exp)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-sm text-[#17211D]">{{ $exp->vendor }}</div>
                            <div class="text-xs text-[#66736C]">{{ $exp->category?->name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-sm text-[#17211D] tabular-nums">{{ $currencySymbol }}{{ $exp->gross_amount }}</div>
                            <span class="inline-flex px-1.5 py-0.5 text-[9px] font-bold uppercase rounded {{ $exp->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-900' : 'bg-amber-100 text-amber-900' }}">
                                {{ $exp->payment_status }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-700 line-clamp-2">{{ $exp->description }}</p>

                    <div class="flex items-center justify-between text-xs text-[#66736C] pt-2 border-t border-slate-100">
                        <span>Date: {{ $exp->expense_date ? $exp->expense_date->format('M d, Y') : '-' }}</span>
                        <div class="space-x-2">
                            <a href="{{ route('expenses.show', $exp) }}" class="text-[#0F6B57] font-semibold hover:underline">View</a>
                            @if(auth()->user()->isOwner())
                                <a href="{{ route('expenses.edit', $exp) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs text-[#66736C]">
                    No expenses found.
                </div>
                @endforelse
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Vendor</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Description</th>
                            <th class="px-5 py-3 text-right">Net</th>
                            <th class="px-5 py-3 text-right">Tax</th>
                            <th class="px-5 py-3 text-right">Gross Total</th>
                            <th class="px-5 py-3 text-center">Payment</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">{{ $exp->expense_date ? $exp->expense_date->format('M d, Y') : '-' }}</td>
                            <td class="px-5 py-3.5 font-bold text-[#17211D]">
                                <a href="{{ route('expenses.show', $exp) }}" class="hover:text-[#0F6B57]">
                                    {{ $exp->vendor }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C]">{{ $exp->category?->name }}</td>
                            <td class="px-5 py-3.5 text-[#66736C] max-w-xs truncate">{{ $exp->description }}</td>
                            <td class="px-5 py-3.5 text-right tabular-nums">{{ $currencySymbol }}{{ $exp->net_amount }}</td>
                            <td class="px-5 py-3.5 text-right tabular-nums">
                                {{ $currencySymbol }}{{ $exp->tax_amount }}
                                @if($exp->tax_recoverable)
                                    <span class="text-[9px] text-emerald-700 font-bold ml-0.5" title="Recoverable Input Tax">(Rec)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-[#17211D] tabular-nums">
                                {{ $currencySymbol }}{{ $exp->gross_amount }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $exp->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                    {{ $exp->payment_status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('expenses.show', $exp) }}" class="text-[#0F6B57] font-semibold hover:underline">View</a>
                                @if(auth()->user()->isOwner())
                                    <a href="{{ route('expenses.edit', $exp) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-[#66736C]">
                                No expenses found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="p-4 border-t border-[#E3E7E4]">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
