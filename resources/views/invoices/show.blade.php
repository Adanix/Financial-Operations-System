<x-layouts.app>
    <x-slot:header>{{ $invoice->invoice_number ?: 'Draft Invoice #' . $invoice->id }}</x-slot:header>

    @php
        $st = $invoice->display_status;
        $customer = $invoice->customer_snapshot ?: [
            'name' => $invoice->customer?->name,
            'company_name' => $invoice->customer?->company_name,
            'billing_address' => $invoice->customer?->billing_address,
            'email' => $invoice->customer?->email,
            'phone' => $invoice->customer?->phone,
            'tax_number' => $invoice->customer?->tax_number,
        ];
        $bank = $invoice->bank_snapshot ?: [
            'bank_name' => $settings->bank_name,
            'account_holder' => $settings->account_holder,
            'account_number' => $settings->account_number,
            'swift_bic' => $settings->swift_bic,
            'branch' => $settings->branch,
            'payment_instructions' => $settings->payment_instructions,
        ];
    @endphp

    <div x-data="{
        recordPaymentModal: false,
        emailModal: false,
        voidModal: false,
        reversePaymentModal: false,
        activePaymentId: null,
        activePaymentAmount: null,
    }" class="space-y-6">

        <!-- Header Action Card -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-[#17211D]">
                        {{ $invoice->invoice_number ?: 'Draft #' . $invoice->id }}
                    </h1>
                    <span class="inline-flex px-2.5 py-0.5 text-xs font-bold uppercase rounded-full
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
                <div class="text-xs text-[#66736C] mt-1 flex flex-wrap gap-4">
                    <span>Issued: <strong class="text-[#17211D]">{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : '-' }}</strong></span>
                    <span>Due: <strong class="text-[#17211D]">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</strong></span>
                    @if($invoice->reference)
                        <span>Ref: <strong class="text-[#17211D]">{{ $invoice->reference }}</strong></span>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 self-stretch md:self-auto justify-end">
                <a href="{{ route('invoices.preview', $invoice) }}" target="_blank"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    PDF Preview
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}"
                    class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Download PDF
                </a>

                @if(auth()->user()->isOwner())
                    @if($invoice->status === 'draft')
                        <a href="{{ route('invoices.edit', $invoice) }}"
                            class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Edit Draft
                        </a>
                        <form action="{{ route('invoices.issue', $invoice) }}" method="POST" onsubmit="return confirm('Officially issue this invoice? A sequential invoice number and immutable snapshot will be created.');">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                Issue Official Invoice
                            </button>
                        </form>
                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Delete this draft invoice permanently?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                                Delete
                            </button>
                        </form>
                    @elseif(in_array($invoice->status, ['issued', 'partially_paid']))
                        <button type="button" @click="recordPaymentModal = true"
                            class="px-4 py-2 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl shadow-xs transition-colors cursor-pointer">
                            Record Payment
                        </button>
                        <button type="button" @click="emailModal = true"
                            class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                            Send Email
                        </button>
                        <button type="button" @click="voidModal = true"
                            class="px-3 py-2 text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                            Void Invoice
                        </button>
                    @elseif($invoice->status === 'paid')
                        <button type="button" @click="emailModal = true"
                            class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                            Send Email
                        </button>
                    @endif
                @endif
            </div>
        </div>

        @if($invoice->status === 'void' && $invoice->void_reason)
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-200 text-xs text-slate-700 flex items-start gap-2">
            <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <strong>Invoice Voided on {{ $invoice->voided_at ? $invoice->voided_at->format('M d, Y H:i') : '' }}:</strong>
                <p class="mt-0.5">{{ $invoice->void_reason }}</p>
            </div>
        </div>
        @endif

        <!-- Customer & Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#66736C]">Billed To</div>
                <div class="text-sm font-bold text-[#17211D]">{{ $customer['name'] }}</div>
                @if(!empty($customer['company_name']))
                    <div class="text-xs text-[#66736C]">{{ $customer['company_name'] }}</div>
                @endif
                <div class="text-xs text-[#66736C] leading-relaxed pt-1">
                    {!! nl2br(e($customer['billing_address'])) !!}
                </div>
                @if(!empty($customer['email']))
                    <div class="text-xs text-[#66736C] pt-1">Email: {{ $customer['email'] }}</div>
                @endif
                @if(!empty($customer['tax_number']))
                    <div class="text-xs text-[#66736C]">Tax ID: {{ $customer['tax_number'] }}</div>
                @endif
            </div>

            <div class="bg-white border border-[#E3E7E4] rounded-2xl p-5 shadow-xs space-y-2">
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#66736C]">Payment & Terms</div>
                <div class="text-xs space-y-1">
                    <div>Pricing Mode: <strong>{{ ucfirst($invoice->pricing_mode) }} Tax</strong></div>
                    <div>Currency: <strong>{{ $invoice->currency_code }} ({{ $invoice->currency_symbol }})</strong></div>
                    <div>Terms: <strong>{{ $invoice->payment_terms_text ?: 'Standard' }}</strong></div>
                </div>

                <!-- Bank box preview -->
                <div class="mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                    <div class="text-[10px] font-bold uppercase text-[#0F6B57]">Bank Transfer Wire Details</div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Bank:</span>
                        <span class="font-medium text-slate-800">{{ $bank['bank_name'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Account Holder:</span>
                        <span class="font-medium text-slate-800">{{ $bank['account_holder'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Account #:</span>
                        <span class="font-bold text-slate-900 font-mono">{{ $bank['account_number'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#E3E7E4]">
                <h3 class="text-sm font-bold text-[#17211D]">Invoice Line Items</h3>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">#</th>
                            <th class="px-5 py-3">Description</th>
                            <th class="px-5 py-3 text-center">Qty</th>
                            <th class="px-5 py-3 text-right">Unit Price</th>
                            <th class="px-5 py-3 text-center">Tax</th>
                            <th class="px-5 py-3 text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @foreach($invoice->items as $idx => $it)
                        <tr>
                            <td class="px-5 py-3.5 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-[#17211D]">{{ $it->description }}</div>
                                @if(\App\Services\DecimalMath::comp($it->line_discount_amount, '0') > 0)
                                    <div class="text-[11px] text-rose-600">Line discount: -{{ $invoice->currency_symbol }}{{ $it->line_discount_amount }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center font-medium">{{ (float)$it->quantity }} {{ $it->unit }}</td>
                            <td class="px-5 py-3.5 text-right font-medium tabular-nums">{{ $invoice->currency_symbol }}{{ $it->unit_price }}</td>
                            <td class="px-5 py-3.5 text-center">{{ (float)$it->tax_rate }}%</td>
                            <td class="px-5 py-3.5 text-right font-bold text-[#17211D] tabular-nums">{{ $invoice->currency_symbol }}{{ $it->line_total }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary Box -->
            <div class="p-5 sm:p-6 border-t border-[#E3E7E4] bg-slate-50/50 flex flex-col items-end">
                <div class="w-full sm:w-80 space-y-2 text-xs">
                    <div class="flex justify-between text-[#66736C]">
                        <span>Gross Subtotal:</span>
                        <span class="font-medium text-[#17211D] tabular-nums">{{ $invoice->currency_symbol }}{{ $invoice->gross_subtotal }}</span>
                    </div>
                    @if(\App\Services\DecimalMath::comp($invoice->discount_total, '0') > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Discounts:</span>
                        <span class="font-medium tabular-nums">-{{ $invoice->currency_symbol }}{{ $invoice->discount_total }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-[#66736C]">
                        <span>Taxable Subtotal:</span>
                        <span class="font-medium text-[#17211D] tabular-nums">{{ $invoice->currency_symbol }}{{ $invoice->taxable_subtotal }}</span>
                    </div>
                    <div class="flex justify-between text-[#66736C]">
                        <span>Tax Total:</span>
                        <span class="font-medium text-[#17211D] tabular-nums">{{ $invoice->currency_symbol }}{{ $invoice->tax_total }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-[#0F6B57] pt-2 border-t border-slate-300">
                        <span>Total ({{ $invoice->currency_code }}):</span>
                        <span class="tabular-nums">{{ $invoice->currency_symbol }}{{ $invoice->total }}</span>
                    </div>
                    @if(\App\Services\DecimalMath::comp($invoice->amount_paid, '0') > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Amount Paid:</span>
                        <span class="font-semibold tabular-nums">-{{ $invoice->currency_symbol }}{{ $invoice->amount_paid }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-[#17211D] pt-2 border-t-2 border-[#17211D]">
                        <span>Balance Due:</span>
                        <span class="tabular-nums {{ (float)$invoice->outstanding_balance > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                            {{ $invoice->currency_symbol }}{{ $invoice->outstanding_balance }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Ledger Section -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#E3E7E4] flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#17211D]">Payment History</h3>
                    <p class="text-xs text-[#66736C]">Recorded customer payments against this invoice.</p>
                </div>
                @if(auth()->user()->isOwner() && in_array($invoice->status, ['issued', 'partially_paid']))
                    <button type="button" @click="recordPaymentModal = true"
                        class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl transition-colors cursor-pointer">
                        + Record Payment
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto w-full">
                <table class="min-w-full divide-y divide-[#E3E7E4] text-xs text-left">
                    <thead class="bg-[#F6F7F5] text-[#66736C] uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Method</th>
                            <th class="px-5 py-3">Reference</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E3E7E4]">
                        @forelse($invoice->payments as $pmt)
                        <tr>
                            <td class="px-5 py-3.5">{{ $pmt->payment_date ? $pmt->payment_date->format('M d, Y') : '-' }}</td>
                            <td class="px-5 py-3.5 capitalize">{{ str_replace('_', ' ', $pmt->method) }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $pmt->reference ?: '-' }}</td>
                            <td class="px-5 py-3.5 text-right font-bold tabular-nums">{{ $invoice->currency_symbol }}{{ $pmt->amount }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded-full {{ $pmt->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-400 line-through' }}">
                                    {{ $pmt->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($pmt->status === 'completed' && auth()->user()->isOwner())
                                    <button type="button" @click="activePaymentId = {{ $pmt->id }}; activePaymentAmount = '{{ $pmt->amount }}'; reversePaymentModal = true;"
                                        class="text-xs text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                        Reverse
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-[#66736C]">
                                No payments recorded yet for this invoice.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Email Logs Section -->
        @if($invoice->emailLogs->isNotEmpty())
        <div class="bg-white border border-[#E3E7E4] rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#E3E7E4]">
                <h3 class="text-sm font-bold text-[#17211D]">Email Dispatch Log</h3>
            </div>
            <div class="divide-y divide-[#E3E7E4] text-xs">
                @foreach($invoice->emailLogs as $elog)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <div class="font-semibold text-[#17211D]">To: {{ $elog->recipient_email }} — {{ $elog->subject }}</div>
                        <div class="text-[11px] text-[#66736C]">{{ $elog->sent_at ? $elog->sent_at->format('M d, Y H:i:s') : 'N/A' }}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $elog->status === 'sent' ? 'bg-emerald-100 text-emerald-900' : ($elog->status === 'sent_demo' ? 'bg-amber-100 text-amber-900' : 'bg-rose-100 text-rose-900') }}">
                        {{ $elog->status }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- MODAL: Record Payment -->
        <div x-cloak x-show="recordPaymentModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50" @click="recordPaymentModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-[#E3E7E4] max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-[#17211D]">Record Payment</h3>
                    <p class="text-xs text-[#66736C]">Current outstanding balance: <strong>{{ $invoice->currency_symbol }}{{ $invoice->outstanding_balance }}</strong></p>

                    <form action="{{ route('payments.store', $invoice) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Amount ({{ $invoice->currency_symbol }}) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" max="{{ $invoice->outstanding_balance }}" name="amount" value="{{ $invoice->outstanding_balance }}" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Payment Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Method <span class="text-rose-500">*</span></label>
                            <select name="method" required class="w-full px-3 py-2 text-sm rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                                <option value="bank_transfer">Bank Transfer / Wire</option>
                                <option value="card">Credit / Debit Card</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Reference / Transaction ID</label>
                            <input type="text" name="reference" placeholder="e.g. TXN-998811"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Internal Notes</label>
                            <textarea name="notes" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]"></textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="recordPaymentModal = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-emerald-700 hover:bg-emerald-800 rounded-xl cursor-pointer">
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Send Email -->
        <div x-cloak x-show="emailModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50" @click="emailModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-[#E3E7E4] max-w-lg w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-[#17211D]">Email Invoice</h3>
                    <p class="text-xs text-[#66736C]">The invoice PDF will be attached. In Demo Mode, the dispatch is simulated and logged.</p>

                    <form action="{{ route('invoices.email', $invoice) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Recipient Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="recipient_email" value="{{ $customer['email'] ?? '' }}" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Subject <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject" value="Invoice {{ $invoice->invoice_number }} from {{ $settings->business_name }}" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Message Body <span class="text-rose-500">*</span></label>
                            <textarea name="body_message" rows="4" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-[#0F6B57]">Dear {{ $customer['name'] }},&#10;&#10;Please find attached invoice {{ $invoice->invoice_number }} for your review. Balance due is {{ $invoice->currency_symbol }}{{ $invoice->outstanding_balance }}.&#10;&#10;Thank you for your business!</textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="emailModal = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl cursor-pointer">
                                Send Invoice Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Void Invoice -->
        <div x-cloak x-show="voidModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50" @click="voidModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-[#E3E7E4] max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-rose-700">Void Invoice {{ $invoice->invoice_number }}</h3>
                    <p class="text-xs text-[#66736C]">Voiding permanently cancels this invoice and excludes it from revenue and receivables. Any active payments must be reversed first.</p>

                    <form action="{{ route('invoices.void', $invoice) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Reason for Voiding <span class="text-rose-500">*</span></label>
                            <textarea name="void_reason" rows="3" required placeholder="e.g. Cancelled scope, client billing duplicate..."
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="voidModal = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl cursor-pointer">
                                Confirm Void
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Reverse Payment -->
        <div x-cloak x-show="reversePaymentModal" class="relative z-50">
            <div class="fixed inset-0 bg-slate-900/50" @click="reversePaymentModal = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-[#E3E7E4] max-w-md w-full p-6 shadow-xl space-y-4">
                    <h3 class="text-base font-bold text-rose-700">Reverse Payment</h3>
                    <p class="text-xs text-[#66736C]">Reversing will restore the invoice balance and audit the action.</p>

                    <form :action="'/payments/' + activePaymentId + '/reverse'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#17211D] mb-1">Reversal Reason <span class="text-rose-500">*</span></label>
                            <textarea name="reversal_reason" rows="3" required placeholder="e.g. Check bounced, client refunded due to wire dispute..."
                                class="w-full px-3 py-2 text-xs rounded-xl border border-[#E3E7E4] focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" @click="reversePaymentModal = false" class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel</button>
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
