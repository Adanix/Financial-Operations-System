<x-layouts.app>
    <x-slot:header>New Invoice Builder</x-slot:header>

    <div x-data="invoiceBuilder()" class="space-y-6">
        <form action="{{ route('invoices.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Invoice Parameters & Line Items -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Basic Metadata Card -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#66736C]">Invoice Information</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Customer Selection -->
                            <div class="sm:col-span-2">
                                <label for="customer_id" class="block text-xs font-semibold text-[#17211D] mb-1">
                                    Customer <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <select name="customer_id" id="customer_id" required
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                                        <option value="">-- Select Customer --</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ (string)old('customer_id', request('customer_id')) === (string)$c->id ? 'selected' : '' }}>
                                                {{ $c->name }} {{ $c->company_name ? "({$c->company_name})" : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('customers.create') }}" target="_blank" title="Create Customer in new tab"
                                        class="p-2.5 bg-slate-100 hover:bg-slate-200 text-[#17211D] rounded-xl shrink-0 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </a>
                                </div>
                                @error('customer_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Issue Date -->
                            <div>
                                <label for="issue_date" class="block text-xs font-semibold text-[#17211D] mb-1">Issue Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" required
                                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                                @error('issue_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label for="due_date" class="block text-xs font-semibold text-[#17211D] mb-1">Due Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="due_date" id="due_date" value="{{ old('due_date', now()->addDays($settings->default_payment_terms)->toDateString()) }}" required
                                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                                @error('due_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Pricing Mode -->
                            <div>
                                <label for="pricing_mode" class="block text-xs font-semibold text-[#17211D] mb-1">Tax Pricing Mode <span class="text-rose-500">*</span></label>
                                <select name="pricing_mode" id="pricing_mode" x-model="pricingMode"
                                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                                    <option value="exclusive">Exclusive (Tax added on top of unit price)</option>
                                    <option value="inclusive">Inclusive (Unit price includes tax)</option>
                                </select>
                                @error('pricing_mode') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Reference / PO -->
                            <div>
                                <label for="reference" class="block text-xs font-semibold text-[#17211D] mb-1">Reference / PO Number</label>
                                <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="e.g. PO-2026-99"
                                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Card -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-[#66736C]">Line Items</h2>
                            <button type="button" @click="addLine()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[#0F6B57] bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Line</span>
                            </button>
                        </div>

                        <!-- Dynamic Lines -->
                        <div class="space-y-4">
                            <template x-for="(line, idx) in lines" :key="idx">
                                <div class="p-4 rounded-xl border border-[#E3E7E4] bg-slate-50/50 space-y-3 relative group">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#17211D]" x-text="'Line #' + (idx + 1)"></span>
                                        <button type="button" @click="removeLine(idx)" x-show="lines.length > 1"
                                            class="text-xs text-rose-500 hover:text-rose-700 font-medium cursor-pointer">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                        <!-- Description -->
                                        <div class="sm:col-span-6">
                                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">Description <span class="text-rose-500">*</span></label>
                                            <input type="text" :name="'items[' + idx + '][description]'" x-model="line.description" required placeholder="Service or product description"
                                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                                        </div>

                                        <!-- Quantity -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">Qty</label>
                                            <input type="number" step="0.001" min="0.001" :name="'items[' + idx + '][quantity]'" x-model.number="line.quantity" required
                                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57] text-center">
                                        </div>

                                        <!-- Unit -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">Unit</label>
                                            <input type="text" :name="'items[' + idx + '][unit]'" x-model="line.unit" placeholder="hrs, pcs"
                                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                                        </div>

                                        <!-- Unit Price -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">Price ({{ $settings->currency_symbol }})</label>
                                            <input type="number" step="0.01" min="0" :name="'items[' + idx + '][unit_price]'" x-model.number="line.unit_price" required
                                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57] text-right font-medium">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1 border-t border-slate-200/60">
                                        <!-- Line Discount -->
                                        <div class="sm:col-span-4">
                                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">Line Discount ({{ $settings->currency_symbol }})</label>
                                            <input type="number" step="0.01" min="0" :name="'items[' + idx + '][line_discount_amount]'" x-model.number="line.line_discount_amount"
                                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57] text-right">
                                        </div>

                                        <!-- Tax Rate -->
                                        <div class="sm:col-span-4">
                                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">Tax Rate</label>
                                            <select :name="'items[' + idx + '][tax_rate_id]'" x-model="line.tax_rate_id" @change="updateLineTax(idx)"
                                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                                                <option value="">No Tax (0%)</option>
                                                <template x-for="t in availableTaxRates" :key="t.id">
                                                    <option :value="t.id" x-text="t.name + ' (' + Number(t.rate) + '%)'"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Computed Line Gross -->
                                        <div class="sm:col-span-4 flex items-center justify-end text-xs text-[#66736C] pt-4">
                                            Line Total:&nbsp;<strong class="text-[#17211D] tabular-nums" x-text="'{{ $settings->currency_symbol }}' + computeLineTotal(line).toFixed(2)"></strong>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Notes & Terms -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
                        <div>
                            <label for="notes" class="block text-xs font-semibold text-[#17211D] mb-1">Notes / Client Message</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">{{ old('notes', $settings->default_notes) }}</textarea>
                        </div>

                        <div>
                            <label for="payment_terms_text" class="block text-xs font-semibold text-[#17211D] mb-1">Payment Terms</label>
                            <input type="text" name="payment_terms_text" id="payment_terms_text" value="{{ old('payment_terms_text', "Payment due within {$settings->default_payment_terms} days.") }}"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>
                    </div>
                </div>

                <!-- Right Col: Discount Options & Authoritative Real-Time Summary -->
                <div class="space-y-6">
                    <!-- Invoice Level Discount Card -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#66736C]">Invoice-Level Discount</h3>

                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                                <input type="radio" name="invoice_discount_type" value="none" x-model="discountType" class="text-[#0F6B57] focus:ring-[#0F6B57]">
                                <span>None</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                                <input type="radio" name="invoice_discount_type" value="percent" x-model="discountType" class="text-[#0F6B57] focus:ring-[#0F6B57]">
                                <span>Percent %</span>
                            </label>
                            <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                                <input type="radio" name="invoice_discount_type" value="fixed" x-model="discountType" class="text-[#0F6B57] focus:ring-[#0F6B57]">
                                <span>Fixed $</span>
                            </label>
                        </div>

                        <div x-show="discountType !== 'none'" class="pt-2">
                            <label class="block text-[11px] font-semibold text-[#66736C] mb-1">
                                Discount Value <span x-text="discountType === 'percent' ? '(%)' : '({{ $settings->currency_symbol }})'"></span>
                            </label>
                            <input type="number" step="0.01" min="0" name="invoice_discount_value" x-model.number="discountValue"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                        </div>
                    </div>

                    <!-- Live Calculation Summary Panel -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#66736C] border-b border-[#E3E7E4] pb-2">Financial Preview</h3>

                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between text-[#66736C]">
                                <span>Gross Subtotal:</span>
                                <span class="font-medium text-[#17211D] tabular-nums" x-text="'{{ $settings->currency_symbol }}' + grossSubtotal().toFixed(2)"></span>
                            </div>

                            <div class="flex justify-between text-[#66736C]" x-show="lineDiscountTotal() > 0">
                                <span>Line Discounts:</span>
                                <span class="text-rose-600 font-medium tabular-nums" x-text="'-{{ $settings->currency_symbol }}' + lineDiscountTotal().toFixed(2)"></span>
                            </div>

                            <div class="flex justify-between text-[#66736C]" x-show="invoiceDiscountTotal() > 0">
                                <span>Invoice Discount:</span>
                                <span class="text-rose-600 font-medium tabular-nums" x-text="'-{{ $settings->currency_symbol }}' + invoiceDiscountTotal().toFixed(2)"></span>
                            </div>

                            <div class="flex justify-between text-[#66736C] pt-2 border-t border-[#F0F2F0]">
                                <span>Taxable Base:</span>
                                <span class="font-medium text-[#17211D] tabular-nums" x-text="'{{ $settings->currency_symbol }}' + taxableBaseTotal().toFixed(2)"></span>
                            </div>

                            <div class="flex justify-between text-[#66736C]">
                                <span>Estimated Tax:</span>
                                <span class="font-medium text-[#17211D] tabular-nums" x-text="'{{ $settings->currency_symbol }}' + taxTotal().toFixed(2)"></span>
                            </div>

                            <div class="flex justify-between text-base font-bold text-[#0F6B57] pt-3 border-t-2 border-[#0F6B57]">
                                <span>Grand Total:</span>
                                <span class="tabular-nums" x-text="'{{ $settings->currency_symbol }}' + grandTotal().toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E3E7E4] space-y-2">
                            <button type="submit"
                                class="w-full py-3 px-4 rounded-xl font-semibold text-xs text-white bg-[#0F6B57] hover:bg-[#0B5546] shadow-xs transition-colors cursor-pointer text-center">
                                Save as Draft Invoice
                            </button>
                            <a href="{{ route('invoices.index') }}" class="block text-center text-xs text-slate-600 hover:text-slate-900 py-1">
                                Cancel & Discard
                            </a>
                        </div>
                    </div>

                    <!-- Instructions hint box -->
                    <div class="p-4 rounded-xl bg-slate-100 text-xs text-[#66736C] space-y-1">
                        <strong class="text-[#17211D]">Note on Drafts:</strong>
                        <p>Draft invoices do not consume a sequential invoice number until they are officially issued. You can review, preview PDF, and verify all lines first.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Alpine.js Invoice Calculator Script -->
    <script>
        function invoiceBuilder() {
            return {
                pricingMode: '{{ old('pricing_mode', $settings->default_pricing_mode) }}',
                discountType: '{{ old('invoice_discount_type', 'none') }}',
                discountValue: {{ (float) old('invoice_discount_value', 0) }},
                availableTaxRates: @json($taxRates),
                lines: [
                    {
                        description: '',
                        quantity: 1,
                        unit: '',
                        unit_price: 0,
                        line_discount_amount: 0,
                        tax_rate_id: '{{ $settings->default_tax_rate_id }}',
                        tax_rate: {{ $settings->default_tax_rate_id ? ($taxRates->find($settings->default_tax_rate_id)?->rate ?? 0) : 0 }}
                    }
                ],

                addLine() {
                    this.lines.push({
                        description: '',
                        quantity: 1,
                        unit: '',
                        unit_price: 0,
                        line_discount_amount: 0,
                        tax_rate_id: '{{ $settings->default_tax_rate_id }}',
                        tax_rate: {{ $settings->default_tax_rate_id ? ($taxRates->find($settings->default_tax_rate_id)?->rate ?? 0) : 0 }}
                    });
                },

                removeLine(idx) {
                    if (this.lines.length > 1) {
                        this.lines.splice(idx, 1);
                    }
                },

                updateLineTax(idx) {
                    const tid = this.lines[idx].tax_rate_id;
                    const found = this.availableTaxRates.find(t => String(t.id) === String(tid));
                    this.lines[idx].tax_rate = found ? Number(found.rate) : 0;
                },

                computeLineGross(line) {
                    return Math.max(0, (Number(line.quantity) || 0) * (Number(line.unit_price) || 0));
                },

                computeLineNet(line) {
                    const gross = this.computeLineGross(line);
                    const disc = Math.min(gross, Math.max(0, Number(line.line_discount_amount) || 0));
                    return gross - disc;
                },

                computeLineTotal(line) {
                    const net = this.computeLineNet(line);
                    const rate = Number(line.tax_rate) || 0;
                    if (this.pricingMode === 'inclusive') {
                        return net;
                    } else {
                        const tax = net * (rate / 100);
                        return net + tax;
                    }
                },

                grossSubtotal() {
                    return this.lines.reduce((acc, l) => acc + this.computeLineGross(l), 0);
                },

                lineDiscountTotal() {
                    return this.lines.reduce((acc, l) => {
                        const gross = this.computeLineGross(l);
                        const disc = Math.min(gross, Math.max(0, Number(l.line_discount_amount) || 0));
                        return acc + disc;
                    }, 0);
                },

                netSubtotal() {
                    return this.lines.reduce((acc, l) => acc + this.computeLineNet(l), 0);
                },

                invoiceDiscountTotal() {
                    const net = this.netSubtotal();
                    if (this.discountType === 'percent') {
                        const pct = Math.min(100, Math.max(0, Number(this.discountValue) || 0));
                        return net * (pct / 100);
                    } else if (this.discountType === 'fixed') {
                        return Math.min(net, Math.max(0, Number(this.discountValue) || 0));
                    }
                    return 0;
                },

                taxableBaseTotal() {
                    return Math.max(0, this.netSubtotal() - this.invoiceDiscountTotal());
                },

                taxTotal() {
                    const netSub = this.netSubtotal();
                    const invDisc = this.invoiceDiscountTotal();
                    if (netSub <= 0) return 0;

                    let totalTax = 0;
                    this.lines.forEach(l => {
                        const lineNet = this.computeLineNet(l);
                        const alloc = (lineNet / netSub) * invDisc;
                        const taxableBase = Math.max(0, lineNet - alloc);
                        const rate = Number(l.tax_rate) || 0;

                        if (this.pricingMode === 'inclusive') {
                            if (rate > 0) {
                                totalTax += taxableBase * (rate / (100 + rate));
                            }
                        } else {
                            totalTax += taxableBase * (rate / 100);
                        }
                    });
                    return totalTax;
                },

                grandTotal() {
                    if (this.pricingMode === 'inclusive') {
                        return this.taxableBaseTotal();
                    } else {
                        return this.taxableBaseTotal() + this.taxTotal();
                    }
                }
            };
        }
    </script>
</x-layouts.app>
