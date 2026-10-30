<x-layouts.app>
    <x-slot:header>Customers</x-slot:header>

    <div class="space-y-4">
        <!-- Top Filter & Action Bar -->
        <div class="bg-white border border-[#E3E7E4] p-4 rounded-2xl shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form action="{{ route('customers.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name, company, email..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="active" onchange="this.form.submit()" class="text-xs py-2 px-3 rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    <option value="">All Statuses</option>
                    <option value="1" {{ $activeFilter === '1' ? 'selected' : '' }}>Active Customers</option>
                    <option value="0" {{ $activeFilter === '0' ? 'selected' : '' }}>Inactive / Archived</option>
                </select>

                <button type="submit" class="px-3 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-[#17211D] rounded-xl transition-colors cursor-pointer">
                    Filter
                </button>
                @if($search || $activeFilter !== null)
                    <a href="{{ route('customers.index') }}" class="px-3 py-2 text-xs text-[#66736C] hover:text-[#17211D]">Reset</a>
                @endif
            </form>

            @if(auth()->user()->isOwner())
                <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Customer</span>
                </a>
            @endif
        </div>

        <!-- Desktop Table & Mobile Cards -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <!-- Mobile View: Stacked Cards -->
            <div class="divide-y divide-[#E3E7E4] md:hidden">
                @forelse($customers as $c)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('customers.show', $c) }}" class="font-bold text-sm text-[#0F6B57] hover:underline">
                                {{ $c->name }}
                            </a>
                            @if($c->company_name)
                                <div class="text-xs text-[#66736C]">{{ $c->company_name }}</div>
                            @endif
                        </div>
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full {{ $c->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="text-xs text-[#66736C] space-y-0.5">
                        @if($c->email) <div>Email: {{ $c->email }}</div> @endif
                        @if($c->phone) <div>Phone: {{ $c->phone }}</div> @endif
                        <div>Invoices: <strong>{{ $c->invoices_count }}</strong></div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 text-xs">
                        <a href="{{ route('customers.show', $c) }}" class="text-[#0F6B57] font-semibold hover:underline">View Details</a>
                        @if(auth()->user()->isOwner())
                            <a href="{{ route('customers.edit', $c) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs text-[#66736C]">
                    No customers found matching your criteria.
                </div>
                @endforelse
            </div>

            <!-- Tablet / Desktop View: Full Responsive Table -->
            <div class="hidden md:block overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Contact</th>
                            <th class="px-5 py-3">Address</th>
                            <th class="px-5 py-3 text-center">Invoices</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @forelse($customers as $c)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('customers.show', $c) }}" class="font-bold text-[#17211D] hover:text-[#0F6B57]">
                                    {{ $c->name }}
                                </a>
                                @if($c->company_name)
                                    <div class="text-[11px] text-[#66736C]">{{ $c->company_name }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C]">
                                <div>{{ $c->email ?: '-' }}</div>
                                <div class="text-[11px]">{{ $c->phone }}</div>
                            </td>
                            <td class="px-5 py-3.5 text-[#66736C] max-w-xs truncate">
                                {{ $c->billing_address }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-bold">
                                {{ $c->invoices_count }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full {{ $c->is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('customers.show', $c) }}" class="text-[#0F6B57] font-semibold hover:underline">View</a>
                                @if(auth()->user()->isOwner())
                                    <a href="{{ route('customers.edit', $c) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-[#66736C]">
                                No customers found matching your criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="p-4 border-t border-[#E3E7E4]">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
