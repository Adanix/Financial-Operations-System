<x-layouts.app>
    <x-slot:header>System & Business Settings</x-slot:header>

    <div class="space-y-6">
        <!-- Tab Navigation -->
        <div class="border-b border-[#E3E7E4]">
            <nav class="flex space-x-6 overflow-x-auto text-xs font-semibold">
                <a href="{{ route('settings.index', ['tab' => 'profile']) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'profile' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Business Profile
                </a>
                <a href="{{ route('settings.index', ['tab' => 'currency']) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'currency' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Currency & Locale
                </a>
                <a href="{{ route('settings.index', ['tab' => 'bank']) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'bank' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Bank Transfer Details
                </a>
                <a href="{{ route('settings.index', ['tab' => 'invoicing']) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'invoicing' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Invoicing Defaults
                </a>
                <a href="{{ route('settings.index', ['tab' => 'categories']) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'categories' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Expense Categories
                </a>
                <a href="{{ route('settings.index', ['tab' => 'audit']) }}"
                   class="pb-3 border-b-2 whitespace-nowrap {{ $tab === 'audit' ? 'border-[#0F6B57] text-[#0F6B57]' : 'border-transparent text-[#66736C] hover:text-[#17211D]' }}">
                    Audit Trail
                </a>
            </nav>
        </div>

        @if(!auth()->user()->isOwner())
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                You are logged in as a Reviewer (Read-Only). Configuration changes require an Owner account.
            </div>
        @endif

        <!-- ========================================== -->
        <!-- TAB 1: BUSINESS PROFILE                    -->
        <!-- ========================================== -->
        @if($tab === 'profile')
            <div class="max-w-2xl bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[#17211D]">Business Identity</h2>
                    <p class="text-xs text-[#66736C]">These details appear on your invoice headers and official correspondence.</p>
                </div>

                <form action="{{ route('settings.update', 'profile') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="business_name" class="block text-xs font-semibold text-[#17211D] mb-1">Trading / Brand Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="business_name" id="business_name" value="{{ old('business_name', $settings->business_name) }}" required
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                            @error('business_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="legal_name" class="block text-xs font-semibold text-[#17211D] mb-1">Legal Registered Name</label>
                            <input type="text" name="legal_name" id="legal_name" value="{{ old('legal_name', $settings->legal_name) }}"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-semibold text-[#17211D] mb-1">Official Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $settings->email) }}" required
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                            @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-semibold text-[#17211D] mb-1">Contact Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $settings->phone) }}"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="website" class="block text-xs font-semibold text-[#17211D] mb-1">Website URL</label>
                            <input type="url" name="website" id="website" value="{{ old('website', $settings->website) }}" placeholder="https://..."
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>

                        <div>
                            <label for="tax_number" class="block text-xs font-semibold text-[#17211D] mb-1">Tax / VAT Registration #</label>
                            <input type="text" name="tax_number" id="tax_number" value="{{ old('tax_number', $settings->tax_number) }}"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-semibold text-[#17211D] mb-1">Business Address</label>
                        <textarea name="address" id="address" rows="3" {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">{{ old('address', $settings->address) }}</textarea>
                    </div>

                    @if(auth()->user()->isOwner())
                        <div class="pt-4 border-t border-[#E3E7E4] flex justify-end">
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                Save Profile
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        <!-- ========================================== -->
        <!-- TAB 2: CURRENCY & LOCALE                   -->
        <!-- ========================================== -->
        @elseif($tab === 'currency')
            <div class="max-w-2xl bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[#17211D]">Currency & Regional Settings</h2>
                    <p class="text-xs text-[#66736C]">SoloLedger operates in a single functional currency. Changing decimals or currency does not retroactively alter locked invoice snapshots.</p>
                </div>

                <form action="{{ route('settings.update', 'currency') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="currency_code" class="block text-xs font-semibold text-[#17211D] mb-1">Currency Code (ISO) <span class="text-rose-500">*</span></label>
                            <input type="text" name="currency_code" id="currency_code" value="{{ old('currency_code', $settings->currency_code) }}" required maxlength="3"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] uppercase font-mono focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>

                        <div>
                            <label for="currency_symbol" class="block text-xs font-semibold text-[#17211D] mb-1">Currency Symbol <span class="text-rose-500">*</span></label>
                            <input type="text" name="currency_symbol" id="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" required maxlength="5"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>

                        <div>
                            <label for="currency_decimals" class="block text-xs font-semibold text-[#17211D] mb-1">Decimal Places <span class="text-rose-500">*</span></label>
                            <select name="currency_decimals" id="currency_decimals" {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                                <option value="2" {{ old('currency_decimals', $settings->currency_decimals) == 2 ? 'selected' : '' }}>2 Decimals (Standard: $12.50)</option>
                                <option value="0" {{ old('currency_decimals', $settings->currency_decimals) == 0 ? 'selected' : '' }}>0 Decimals (e.g. JPY / IDR: ¥1,250)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="timezone" class="block text-xs font-semibold text-[#17211D] mb-1">Timezone <span class="text-rose-500">*</span></label>
                            <input type="text" name="timezone" id="timezone" value="{{ old('timezone', $settings->timezone) }}" required placeholder="e.g. America/New_York, UTC"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                            <p class="text-[11px] text-[#66736C] mt-1">Used for overdue evaluations and midnight cutoffs.</p>
                        </div>

                        <div>
                            <label for="date_format" class="block text-xs font-semibold text-[#17211D] mb-1">Date Format <span class="text-rose-500">*</span></label>
                            <select name="date_format" id="date_format" {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                                <option value="Y-m-d" {{ old('date_format', $settings->date_format) === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2026-10-30)</option>
                                <option value="m/d/Y" {{ old('date_format', $settings->date_format) === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (10/30/2026)</option>
                                <option value="d/m/Y" {{ old('date_format', $settings->date_format) === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (30/10/2026)</option>
                                <option value="M d, Y" {{ old('date_format', $settings->date_format) === 'M d, Y' ? 'selected' : '' }}>Mon DD, YYYY (Oct 30, 2026)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="tax_label" class="block text-xs font-semibold text-[#17211D] mb-1">Tax Nomenclature Label <span class="text-rose-500">*</span></label>
                        <input type="text" name="tax_label" id="tax_label" value="{{ old('tax_label', $settings->tax_label) }}" required placeholder="e.g. VAT, Sales Tax, GST"
                            {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                    </div>

                    @if(auth()->user()->isOwner())
                        <div class="pt-4 border-t border-[#E3E7E4] flex justify-end">
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                Save Regional Settings
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        <!-- ========================================== -->
        <!-- TAB 3: BANK DETAILS                        -->
        <!-- ========================================== -->
        @elseif($tab === 'bank')
            <div class="max-w-2xl bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[#17211D]">Bank Transfer Details</h2>
                    <p class="text-xs text-[#66736C]">These payment coordinates are automatically snapshotted into issued PDF invoices.</p>
                </div>

                <form action="{{ route('settings.update', 'bank') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="block text-xs font-semibold text-[#17211D] mb-1">Bank Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $settings->bank_name) }}" required placeholder="e.g. Silicon Valley Bank"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                            @error('bank_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="account_holder" class="block text-xs font-semibold text-[#17211D] mb-1">Account Holder Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="account_holder" id="account_holder" value="{{ old('account_holder', $settings->account_holder) }}" required
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="account_number" class="block text-xs font-semibold text-[#17211D] mb-1">Account Number / IBAN <span class="text-rose-500">*</span></label>
                            <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $settings->account_number) }}" required
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] font-mono focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>

                        <div>
                            <label for="swift_bic" class="block text-xs font-semibold text-[#17211D] mb-1">SWIFT / BIC Code</label>
                            <input type="text" name="swift_bic" id="swift_bic" value="{{ old('swift_bic', $settings->swift_bic) }}"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] font-mono focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>
                    </div>

                    <div>
                        <label for="branch" class="block text-xs font-semibold text-[#17211D] mb-1">Branch / Routing Number</label>
                        <input type="text" name="branch" id="branch" value="{{ old('branch', $settings->branch) }}"
                            {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                    </div>

                    <div>
                        <label for="payment_instructions" class="block text-xs font-semibold text-[#17211D] mb-1">Remittance Instructions Note</label>
                        <textarea name="payment_instructions" id="payment_instructions" rows="2" placeholder="e.g. Please quote invoice number as payment reference."
                            {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">{{ old('payment_instructions', $settings->payment_instructions) }}</textarea>
                    </div>

                    @if(auth()->user()->isOwner())
                        <div class="pt-4 border-t border-[#E3E7E4] flex justify-end">
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                Save Bank Coordinates
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        <!-- ========================================== -->
        <!-- TAB 4: INVOICING DEFAULTS                  -->
        <!-- ========================================== -->
        @elseif($tab === 'invoicing')
            <div class="max-w-2xl bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
                <div class="mb-6">
                    <h2 class="text-base font-bold text-[#17211D]">Invoicing Presets & Sequencing</h2>
                    <p class="text-xs text-[#66736C]">Configure default terms, tax treatment, and sequential numbering pattern.</p>
                </div>

                <form action="{{ route('settings.update', 'invoicing') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="invoice_prefix" class="block text-xs font-semibold text-[#17211D] mb-1">Invoice Prefix <span class="text-rose-500">*</span></label>
                            <input type="text" name="invoice_prefix" id="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" required maxlength="10"
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] font-mono uppercase focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                            <p class="text-[11px] text-[#66736C] mt-1">Format: {PREFIX}-{YEAR}-{SEQUENCE}</p>
                        </div>

                        <div>
                            <label for="sequence_padding" class="block text-xs font-semibold text-[#17211D] mb-1">Sequence Zero-Padding <span class="text-rose-500">*</span></label>
                            <select name="sequence_padding" id="sequence_padding" {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                                @for($i = 3; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ old('sequence_padding', $settings->sequence_padding) == $i ? 'selected' : '' }}>
                                        {{ $i }} Digits (e.g. {{ str_pad('1', $i, '0', STR_PAD_LEFT) }})
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="default_payment_terms" class="block text-xs font-semibold text-[#17211D] mb-1">Payment Terms (Days) <span class="text-rose-500">*</span></label>
                            <input type="number" min="0" max="180" name="default_payment_terms" id="default_payment_terms" value="{{ old('default_payment_terms', $settings->default_payment_terms) }}" required
                                {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                        </div>

                        <div>
                            <label for="default_pricing_mode" class="block text-xs font-semibold text-[#17211D] mb-1">Default Tax Treatment <span class="text-rose-500">*</span></label>
                            <select name="default_pricing_mode" id="default_pricing_mode" {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                                <option value="exclusive" {{ old('default_pricing_mode', $settings->default_pricing_mode) === 'exclusive' ? 'selected' : '' }}>Exclusive (Tax added)</option>
                                <option value="inclusive" {{ old('default_pricing_mode', $settings->default_pricing_mode) === 'inclusive' ? 'selected' : '' }}>Inclusive (Tax inside)</option>
                            </select>
                        </div>

                        <div>
                            <label for="default_tax_rate_id" class="block text-xs font-semibold text-[#17211D] mb-1">Default Tax Rate</label>
                            <select name="default_tax_rate_id" id="default_tax_rate_id" {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">
                                <option value="">No Default Rate</option>
                                @foreach($taxRates as $tr)
                                    <option value="{{ $tr->id }}" {{ old('default_tax_rate_id', $settings->default_tax_rate_id) == $tr->id ? 'selected' : '' }}>
                                        {{ $tr->name }} ({{ (float)$tr->rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="default_notes" class="block text-xs font-semibold text-[#17211D] mb-1">Default Invoice Terms & Customer Notes</label>
                        <textarea name="default_notes" id="default_notes" rows="3" placeholder="Terms and conditions rendered on invoices by default."
                            {{ !auth()->user()->isOwner() ? 'disabled' : '' }}
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57] disabled:bg-slate-50">{{ old('default_notes', $settings->default_notes) }}</textarea>
                    </div>

                    @if(auth()->user()->isOwner())
                        <div class="pt-4 border-t border-[#E3E7E4] flex justify-end">
                            <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                Save Defaults
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        <!-- ========================================== -->
        <!-- TAB 5: EXPENSE CATEGORIES                  -->
        <!-- ========================================== -->
        @elseif($tab === 'categories')
            <div class="space-y-6" x-data="{ showModal: false }">
                <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-[#E3E7E4] flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h3 class="text-sm font-bold text-[#17211D]">Expense Classification Categories</h3>
                            <p class="text-xs text-[#66736C]">Used to bucket operating costs in Management P&L and expense analytics.</p>
                        </div>
                        @if(auth()->user()->isOwner())
                            <button @click="showModal = true" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                + Add Category
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-4">Category Name</th>
                                    <th class="py-3 px-4">Code</th>
                                    <th class="py-3 px-4 text-center">Active Status</th>
                                    <th class="py-3 px-4 text-right">Expense Count</th>
                                    <th class="py-3 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($categories as $cat)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-3 px-4 font-semibold text-[#17211D]">{{ $cat->name }}</td>
                                        <td class="py-3 px-4 font-mono text-slate-700">{{ $cat->code ?: '-' }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if($cat->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right font-mono text-slate-600">{{ $cat->expenses_count }}</td>
                                        <td class="py-3 px-4 text-right">
                                            @if(auth()->user()->isOwner())
                                                <form action="{{ route('settings.categories.update', $cat) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="name" value="{{ $cat->name }}">
                                                    <input type="hidden" name="code" value="{{ $cat->code }}">
                                                    <input type="hidden" name="is_active" value="{{ $cat->is_active ? '0' : '1' }}">
                                                    <button type="submit" class="text-xs font-semibold text-[#0F6B57] hover:underline cursor-pointer">
                                                        {{ $cat->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-[#66736C]">No categories configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Category Modal -->
                <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showModal = false">
                    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-[#E3E7E4]" @click.outside="showModal = false">
                        <div class="flex items-center justify-between pb-3 border-b border-[#E3E7E4]">
                            <h3 class="text-sm font-bold text-[#17211D]">Add Expense Category</h3>
                            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">&times;</button>
                        </div>
                        <form action="{{ route('settings.categories.store') }}" method="POST" class="space-y-4 pt-4">
                            @csrf
                            <div>
                                <label for="cat_name" class="block text-xs font-semibold text-[#17211D] mb-1">Category Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" id="cat_name" required placeholder="e.g. Legal & Professional"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                            </div>
                            <div>
                                <label for="cat_code" class="block text-xs font-semibold text-[#17211D] mb-1">Category Code</label>
                                <input type="text" name="code" id="cat_code" placeholder="e.g. LEGAL"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] uppercase focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E3E7E4]">
                                <button @click="showModal = false" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                    Create Category
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <!-- ========================================== -->
        <!-- TAB 6: AUDIT TRAIL                         -->
        <!-- ========================================== -->
        @elseif($tab === 'audit')
            <div class="bg-white border border-[#E3E7E4] rounded-2xl overflow-hidden shadow-xs">
                <div class="px-6 py-4 border-b border-[#E3E7E4] bg-slate-50/50">
                    <h3 class="text-sm font-bold text-[#17211D]">Audit Trail & Operational Activity</h3>
                    <p class="text-xs text-[#66736C]">Immutable append-only record of invoice actions, payments, and system edits.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-[#E3E7E4] bg-slate-50 text-[#66736C] font-semibold uppercase tracking-wider">
                                <th class="py-3 px-4">Timestamp</th>
                                <th class="py-3 px-4">User</th>
                                <th class="py-3 px-4">Event</th>
                                <th class="py-3 px-4">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($auditLogs as $log)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap">
                                        {{ $log->created_at->timezone($settings->timezone)->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="py-3 px-4 font-medium text-[#17211D]">
                                        {{ $log->user ? $log->user->name : 'System' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono bg-slate-100 text-slate-700">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-[#66736C]">No audit logs recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($auditLogs->hasPages())
                    <div class="p-4 border-t border-[#E3E7E4]">
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>
