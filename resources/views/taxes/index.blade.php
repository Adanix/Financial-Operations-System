<x-layouts.app>
    <x-slot:header>Taxes & VAT Overview</x-slot:header>

    <div class="space-y-6" x-data="{ showCreateModal: false, editRate: null }">
        <!-- Top Toolbar & Period Presets -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white border border-[#E3E7E4] rounded-2xl p-4 shadow-xs">
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-medium">
                <span class="text-[#66736C] mr-2">Filing Period:</span>
                @php
                    $periods = [
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_quarter' => 'This Quarter',
                        'last_quarter' => 'Last Quarter',
                        'this_year' => 'This Year',
                        'last_year' => 'Last Year',
                    ];
                @endphp
                @foreach($periods as $key => $label)
                    <a href="{{ route('taxes.index', ['period' => $key]) }}"
                       class="px-3 py-1.5 rounded-lg transition-colors {{ $currentPeriod === $key ? 'bg-[#0F6B57] text-white font-semibold' : 'text-[#66736C] hover:bg-slate-100 hover:text-[#17211D]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Custom Range & Action -->
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('taxes.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="period" value="custom">
                    <input type="date" name="from_date" value="{{ $range['from_date'] }}" class="px-2.5 py-1 text-xs rounded-lg border border-[#E3E7E4] bg-white text-[#17211D]">
                    <span class="text-xs text-[#66736C]">to</span>
                    <input type="date" name="to_date" value="{{ $range['to_date'] }}" class="px-2.5 py-1 text-xs rounded-lg border border-[#E3E7E4] bg-white text-[#17211D]">
                    <button type="submit" class="px-3 py-1 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 cursor-pointer">
                        Apply
                    </button>
                </form>

                @if(auth()->user()->isOwner())
                    <button @click="showCreateModal = true" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                        + Add Tax Rate
                    </button>
                @endif
            </div>
        </div>

        <!-- Period Info Banner -->
        <div class="text-xs text-[#66736C] px-1">
            Showing tax calculation from <span class="font-semibold text-[#17211D]">{{ \Carbon\Carbon::parse($range['from_date'])->format('M d, Y') }}</span> to <span class="font-semibold text-[#17211D]">{{ \Carbon\Carbon::parse($range['to_date'])->format('M d, Y') }}</span>. Output tax is recognized upon invoice issuance. Recoverable input tax requires expense claim eligibility.
        </div>

        <!-- Tax Position Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Output Tax -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Output Tax (Collected)</span>
                <div class="text-2xl font-bold text-[#17211D] mt-2">
                    {{ $currencySymbol }}{{ number_format($summary['total_output_tax'], 2) }}
                </div>
                <span class="text-[11px] text-[#66736C] mt-1 block">From issued & paid invoices</span>
            </div>

            <!-- Recoverable Input Tax -->
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs">
                <span class="text-xs font-semibold text-[#66736C] uppercase tracking-wider block">Recoverable Input Tax (Paid)</span>
                <div class="text-2xl font-bold text-[#17211D] mt-2">
                    {{ $currencySymbol }}{{ number_format($summary['total_recoverable_input_tax'], 2) }}
                </div>
                <span class="text-[11px] text-[#66736C] mt-1 block">From eligible business expenses</span>
            </div>

            <!-- Net Position -->
            @php
                $netVal = (float)$summary['estimated_net_tax_position'];
            @endphp
            <div class="bg-white border {{ $netVal >= 0 ? 'border-[#E3E7E4]' : 'border-blue-200' }} rounded-2xl p-5 shadow-xs">
                <span class="text-xs font-semibold {{ $netVal >= 0 ? 'text-[#66736C]' : 'text-blue-700' }} uppercase tracking-wider block">
                    Estimated Net Tax Position
                </span>
                <div class="text-2xl font-bold {{ $netVal >= 0 ? 'text-[#17211D]' : 'text-blue-700' }} mt-2">
                    {{ $currencySymbol }}{{ number_format(abs($netVal), 2) }}
                </div>
                <span class="text-[11px] font-medium {{ $netVal >= 0 ? 'text-amber-700' : 'text-blue-600' }} mt-1 block">
                    {{ $netVal >= 0 ? 'Net Payable to Tax Authority' : 'Net Refund Claimable from Authority' }}
                </span>
            </div>
        </div>

        <!-- Breakdown by Tax Rate Table -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                <h3 class="text-sm font-bold text-[#17211D]">Filing Summary by Tax Rate</h3>
                <p class="text-xs text-[#66736C]">Itemized taxable base and corresponding output/input tax.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Tax Bracket</th>
                            <th class="py-3 px-4 text-right">Taxable Sales</th>
                            <th class="py-3 px-4 text-right">Output Tax</th>
                            <th class="py-3 px-4 text-right">Taxable Purchases</th>
                            <th class="py-3 px-4 text-right">Recoverable Input Tax</th>
                            <th class="py-3 px-4 text-right">Net Position</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($summary['rates'] as $row)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 font-semibold text-[#17211D]">{{ $row['name'] }} ({{ (float)$row['rate'] }}%)</td>
                                <td class="py-3 px-4 text-right font-medium text-slate-700">{{ $currencySymbol }}{{ number_format($row['taxable_sales'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-[#17211D]">{{ $currencySymbol }}{{ number_format($row['output_tax'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-medium text-slate-700">{{ $currencySymbol }}{{ number_format($row['taxable_purchases'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-[#17211D]">{{ $currencySymbol }}{{ number_format($row['recoverable_input_tax'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ (float)$row['net_position'] >= 0 ? 'text-[#17211D]' : 'text-blue-700' }}">
                                    {{ $currencySymbol }}{{ number_format($row['net_position'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-[#66736C]">
                                    No qualifying invoice or expense transactions found for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-[#E3E7E4] bg-slate-50 font-bold text-[#17211D]">
                        <tr>
                            <td class="py-3 px-4">Total</td>
                            <td class="py-3 px-4 text-right">-</td>
                            <td class="py-3 px-4 text-right">{{ $currencySymbol }}{{ number_format($summary['total_output_tax'], 2) }}</td>
                            <td class="py-3 px-4 text-right">-</td>
                            <td class="py-3 px-4 text-right">{{ $currencySymbol }}{{ number_format($summary['total_recoverable_input_tax'], 2) }}</td>
                            <td class="py-3 px-4 text-right {{ $netVal >= 0 ? 'text-[#17211D]' : 'text-blue-700' }}">
                                {{ $currencySymbol }}{{ number_format($summary['estimated_net_tax_position'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tax Rates Configuration -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-[#E3E7E4] flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-bold text-[#17211D]">Configured Tax Rates</h3>
                    <p class="text-xs text-[#66736C]">Active rates available for invoice line items and expense records.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Rate Name</th>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4 text-right">Percentage</th>
                            <th class="py-3 px-4 text-center">Default</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">In-Use Count</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($taxRates as $rate)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 font-semibold text-[#17211D]">
                                    {{ $rate->name }}
                                    @if($rate->description)
                                        <span class="block text-[11px] text-[#66736C] font-normal">{{ $rate->description }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700">{{ $rate->code ?: '-' }}</td>
                                <td class="py-3 px-4 text-right font-bold text-[#17211D]">{{ (float)$rate->rate }}%</td>
                                <td class="py-3 px-4 text-center">
                                    @if($rate->is_default)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            Default
                                        </span>
                                    @elseif(auth()->user()->isOwner() && $rate->is_active)
                                        <form action="{{ route('taxes.default', $rate) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-[11px] text-[#0F6B57] hover:underline cursor-pointer">
                                                Set Default
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[#66736C]">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($rate->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-slate-600 font-mono">
                                    {{ $rate->invoice_items_count + $rate->expenses_count }}
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    @if(auth()->user()->isOwner())
                                        <button @click="editRate = {{ $rate->toJson() }}" class="text-[#0F6B57] font-semibold hover:underline mr-3 cursor-pointer">
                                            Edit
                                        </button>
                                        @if(!$rate->is_default)
                                            <form action="{{ route('taxes.status', $rate) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-slate-600 hover:text-slate-900 font-medium cursor-pointer">
                                                    {{ $rate->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="text-[#66736C] text-[11px]">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Tax Rate Modal -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showCreateModal = false">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#E3E7E4]" @click.outside="showCreateModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-[#E3E7E4]">
                    <h3 class="text-sm font-bold text-[#17211D]">Add New Tax Rate</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                </div>
                <form action="{{ route('taxes.store') }}" method="POST" class="space-y-4 pt-4">
                    @csrf
                    <div>
                        <label for="new_name" class="block text-xs font-semibold text-[#17211D] mb-1">Tax Rate Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="new_name" required placeholder="e.g. Standard VAT, State Tax"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="new_code" class="block text-xs font-semibold text-[#17211D] mb-1">Code</label>
                            <input type="text" name="code" id="new_code" placeholder="e.g. VAT-20"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>
                        <div>
                            <label for="new_rate" class="block text-xs font-semibold text-[#17211D] mb-1">Rate (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" name="rate" id="new_rate" required placeholder="0.00"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>
                    </div>
                    <div>
                        <label for="new_desc" class="block text-xs font-semibold text-[#17211D] mb-1">Description</label>
                        <textarea name="description" id="new_desc" rows="2" placeholder="Optional notes on tax rule applicability"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_default" id="new_is_default" value="1" class="rounded border-[#E3E7E4] text-[#0F6B57] focus:ring-[#0F6B57]">
                        <label for="new_is_default" class="text-xs font-semibold text-[#17211D] cursor-pointer">Set as default rate for new invoices</label>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E3E7E4]">
                        <button @click="showCreateModal = false" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                            Create Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Tax Rate Modal -->
        <div x-show="editRate !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="editRate = null">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-[#E3E7E4]" @click.outside="editRate = null" x-show="editRate !== null">
                <div class="flex items-center justify-between pb-3 border-b border-[#E3E7E4]">
                    <h3 class="text-sm font-bold text-[#17211D]">Edit Tax Rate</h3>
                    <button @click="editRate = null" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                </div>
                <form :action="'/taxes/' + (editRate ? editRate.id : '')" method="POST" class="space-y-4 pt-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-[#17211D] mb-1">Tax Rate Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" :value="editRate ? editRate.name : ''" required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Code</label>
                            <input type="text" name="code" :value="editRate ? editRate.code : ''"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Rate (%) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" name="rate" :value="editRate ? editRate.rate : ''" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#17211D] mb-1">Description</label>
                        <textarea name="description" rows="2" :value="editRate ? editRate.description : ''"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]"></textarea>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_default" id="edit_is_default" value="1" :checked="editRate && editRate.is_default"
                                class="rounded border-[#E3E7E4] text-[#0F6B57] focus:ring-[#0F6B57]">
                            <label for="edit_is_default" class="text-xs font-semibold text-[#17211D] cursor-pointer">Default rate</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editRate && editRate.is_active"
                                class="rounded border-[#E3E7E4] text-[#0F6B57] focus:ring-[#0F6B57]">
                            <label for="edit_is_active" class="text-xs font-semibold text-[#17211D] cursor-pointer">Active</label>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E3E7E4]">
                        <button @click="editRate = null" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
