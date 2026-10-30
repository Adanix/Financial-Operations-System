<x-layouts.app>
    <x-slot:header>New Customer</x-slot:header>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
            <div class="mb-6">
                <h2 class="text-base font-bold text-[#17211D]">Add New Customer</h2>
                <p class="text-xs text-[#66736C]">Billing details used on issued invoices and account statements.</p>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-[#17211D] mb-1">Customer / Contact Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company_name" class="block text-xs font-semibold text-[#17211D] mb-1">Company / Legal Entity Name</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    @error('company_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#17211D] mb-1">Billing Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-[#17211D] mb-1">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="billing_address" class="block text-xs font-semibold text-[#17211D] mb-1">Full Billing Address <span class="text-rose-500">*</span></label>
                    <textarea name="billing_address" id="billing_address" rows="3" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">{{ old('billing_address') }}</textarea>
                    @error('billing_address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tax_number" class="block text-xs font-semibold text-[#17211D] mb-1">Tax ID / VAT Registration Number</label>
                    <input type="text" name="tax_number" id="tax_number" value="{{ old('tax_number') }}"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                    @error('tax_number') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-[#17211D] mb-1">Internal Notes</label>
                    <textarea name="notes" id="notes" rows="2"
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="rounded border-[#E3E7E4] text-[#0F6B57] focus:ring-[#0F6B57]">
                    <label for="is_active" class="text-xs font-medium text-[#17211D] cursor-pointer">Active customer (eligible for new invoices)</label>
                </div>

                <div class="pt-4 border-t border-[#E3E7E4] flex items-center justify-end gap-3">
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
