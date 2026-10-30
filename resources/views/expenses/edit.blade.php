<x-layouts.app>
    <x-slot:header>Edit Expense #{{ $expense->id }}</x-slot:header>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#17211D]">Edit Operating Expense #{{ $expense->id }}</h2>
                    <p class="text-xs text-[#66736C]">Update purchase details, classification, or receipt attachment.</p>
                </div>
                <a href="{{ route('expenses.show', $expense) }}" class="text-xs font-semibold text-[#0F6B57] hover:underline">
                    &larr; Back to Details
                </a>
            </div>

            <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ isPaid: '{{ old('payment_status', $expense->payment_status) }}' }">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Vendor -->
                    <div>
                        <label for="vendor" class="block text-xs font-semibold text-[#17211D] mb-1">Vendor / Payee <span class="text-rose-500">*</span></label>
                        <input type="text" name="vendor" id="vendor" value="{{ old('vendor', $expense->vendor) }}" required placeholder="e.g. Amazon Web Services, WeWork"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        @error('vendor') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="expense_category_id" class="block text-xs font-semibold text-[#17211D] mb-1">Expense Category <span class="text-rose-500">*</span></label>
                        <select name="expense_category_id" id="expense_category_id" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('expense_category_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Expense Date & Amount -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="expense_date" class="block text-xs font-semibold text-[#17211D] mb-1">Expense Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                            class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        @error('expense_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="entered_amount" class="block text-xs font-semibold text-[#17211D] mb-1">Amount <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="entered_amount" id="entered_amount" value="{{ old('entered_amount', $expense->entered_amount) }}" required placeholder="0.00"
                            class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white text-right font-medium focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        @error('entered_amount') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pricing_mode" class="block text-xs font-semibold text-[#17211D] mb-1">Tax Treatment <span class="text-rose-500">*</span></label>
                        <select name="pricing_mode" id="pricing_mode" class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                            <option value="exclusive" {{ old('pricing_mode', $expense->pricing_mode) === 'exclusive' ? 'selected' : '' }}>Exclusive (Tax added)</option>
                            <option value="inclusive" {{ old('pricing_mode', $expense->pricing_mode) === 'inclusive' ? 'selected' : '' }}>Inclusive (Tax inside)</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-semibold text-[#17211D] mb-1">Description / Business Purpose <span class="text-rose-500">*</span></label>
                    <textarea name="description" id="description" rows="2" required placeholder="What was purchased and for which business need?"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">{{ old('description', $expense->description) }}</textarea>
                    @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tax Settings & Recoverability -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="tax_rate_id" class="block text-xs font-semibold text-[#17211D] mb-1">Purchase Tax Rate</label>
                            <select name="tax_rate_id" id="tax_rate_id" class="w-full px-3 py-2 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                                <option value="">No Tax (0%)</option>
                                @foreach($taxRates as $tr)
                                    <option value="{{ $tr->id }}" {{ old('tax_rate_id', $expense->tax_rate_id) == $tr->id ? 'selected' : '' }}>{{ $tr->name }} ({{ (float)$tr->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2 pt-5">
                            <input type="checkbox" name="tax_recoverable" id="tax_recoverable" value="1" {{ old('tax_recoverable', $expense->tax_recoverable) ? 'checked' : '' }}
                                class="rounded border-[#E3E7E4] text-[#0F6B57] focus:ring-[#0F6B57]">
                            <label for="tax_recoverable" class="text-xs font-semibold text-[#17211D] cursor-pointer">Tax is recoverable (Input Tax Claim)</label>
                        </div>
                    </div>
                    <p class="text-[11px] text-[#66736C]">
                        * If marked recoverable, tax enters recoverable input tax and only net amount enters management P&L. If non-recoverable, the full gross amount enters P&L.
                    </p>
                </div>

                <!-- Payment State Controls -->
                <div class="p-4 rounded-xl border border-[#E3E7E4] space-y-3">
                    <div class="flex items-center gap-4">
                        <label class="text-xs font-semibold text-[#17211D]">Payment Status:</label>
                        <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                            <input type="radio" name="payment_status" value="paid" x-model="isPaid" class="text-[#0F6B57] focus:ring-[#0F6B57]">
                            <span>Paid</span>
                        </label>
                        <label class="flex items-center gap-1.5 text-xs cursor-pointer">
                            <input type="radio" name="payment_status" value="unpaid" x-model="isPaid" class="text-[#0F6B57] focus:ring-[#0F6B57]">
                            <span>Unpaid (Accrued)</span>
                        </label>
                    </div>

                    <div x-show="isPaid === 'paid'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label for="payment_date" class="block text-xs font-semibold text-[#17211D] mb-1">Payment Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', optional($expense->payment_date)->format('Y-m-d') ?: now()->toDateString()) }}"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                        </div>

                        <div>
                            <label for="payment_method" class="block text-xs font-semibold text-[#17211D] mb-1">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="w-full px-3 py-1.5 text-xs rounded-lg border border-[#E3E7E4] bg-white focus:outline-none focus:ring-1 focus:ring-[#0F6B57]">
                                <option value="card" {{ old('payment_method', $expense->payment_method) === 'card' ? 'selected' : '' }}>Company Credit / Debit Card</option>
                                <option value="bank_transfer" {{ old('payment_method', $expense->payment_method) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer / Wire</option>
                                <option value="cash" {{ old('payment_method', $expense->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="other" {{ old('payment_method', $expense->payment_method) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Reference & Receipt Upload -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="reference" class="block text-xs font-semibold text-[#17211D] mb-1">Invoice / Receipt Reference #</label>
                        <input type="text" name="reference" id="reference" value="{{ old('reference', $expense->reference) }}" placeholder="e.g. INV-998811"
                            class="w-full px-3.5 py-2 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    </div>

                    <div>
                        <label for="receipt" class="block text-xs font-semibold text-[#17211D] mb-1">Attach / Replace Receipt File</label>
                        @if($expense->receipt_path)
                            <p class="text-xs text-[#0F6B57] mb-1">Current file: {{ $expense->receipt_original_name }}</p>
                        @endif
                        <input type="file" name="receipt" id="receipt" accept=".pdf,.jpg,.jpeg,.png,.webp"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-[#17211D] hover:file:bg-slate-200 cursor-pointer">
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E3E7E4] flex items-center justify-end gap-3">
                    <a href="{{ route('expenses.show', $expense) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                        Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
