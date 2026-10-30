<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number ?: 'Draft Invoice' }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #17211D;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 2px solid #0F6B57;
            padding-bottom: 15px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #0F6B57;
            margin-bottom: 4px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #17211D;
            text-align: right;
            text-transform: uppercase;
        }
        .draft-banner {
            background-color: #FEF3C7;
            color: #92400E;
            text-align: center;
            padding: 8px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 15px;
            border: 1px dashed #F59E0B;
        }
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box {
            vertical-align: top;
            width: 48%;
        }
        .box-title {
            font-size: 11px;
            font-weight: bold;
            color: #66736C;
            text-transform: uppercase;
            margin-bottom: 6px;
            border-bottom: 1px solid #E3E7E4;
            padding-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #F6F7F5;
            color: #17211D;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border-top: 1px solid #E3E7E4;
            border-bottom: 1px solid #E3E7E4;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #F0F2F0;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .totals-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px 6px;
        }
        .totals-table .grand-total {
            border-top: 2px solid #0F6B57;
            border-bottom: 2px solid #0F6B57;
            font-size: 13px;
            font-weight: bold;
            color: #0F6B57;
        }
        .totals-table .balance-due {
            background-color: #F6F7F5;
            font-weight: bold;
            font-size: 12px;
        }
        .bank-box {
            background-color: #F6F7F5;
            border: 1px solid #E3E7E4;
            border-left: 4px solid #0F6B57;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .bank-title {
            font-size: 11px;
            font-weight: bold;
            color: #0F6B57;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .bank-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .bank-details td {
            padding: 2px 4px;
            font-size: 10px;
        }
        .notes-section {
            font-size: 10px;
            color: #66736C;
            margin-top: 15px;
            border-top: 1px solid #E3E7E4;
            padding-top: 8px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #66736C;
            border-top: 1px solid #E3E7E4;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    @if($isDraft)
        <div class="draft-banner">
            *** DRAFT INVOICE — FOR REVIEW ONLY — NOT AN OFFICIAL TAX INVOICE ***
        </div>
    @endif

    <div class="header">
        <table>
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <div class="company-title">{{ $business['business_name'] ?? 'SoloLedger' }}</div>
                    @if(!empty($business['legal_name']))
                        <div>{{ $business['legal_name'] }}</div>
                    @endif
                    @if(!empty($business['address']))
                        <div>{!! nl2br(e($business['address'])) !!}</div>
                    @endif
                    @if(!empty($business['email']))
                        <div>Email: {{ $business['email'] }}</div>
                    @endif
                    @if(!empty($business['phone']))
                        <div>Phone: {{ $business['phone'] }}</div>
                    @endif
                    @if(!empty($business['tax_number']))
                        <div>{{ $business['tax_label'] ?? 'Tax' }} ID: {{ $business['tax_number'] }}</div>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: top; text-align: right;">
                    <div class="invoice-title">
                        {{ $isDraft ? 'DRAFT' : 'INVOICE' }}
                    </div>
                    <table style="width: 100%; margin-top: 8px;">
                        <tr>
                            <td class="text-right" style="color: #66736C; font-weight: bold;">Invoice Number:</td>
                            <td class="text-right" style="font-weight: bold;">{{ $invoice->invoice_number ?: 'DRAFT' }}</td>
                        </tr>
                        <tr>
                            <td class="text-right" style="color: #66736C;">Issue Date:</td>
                            <td class="text-right">{{ $invoice->issue_date ? $invoice->issue_date->format('M d, Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-right" style="color: #66736C;">Due Date:</td>
                            <td class="text-right">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</td>
                        </tr>
                        @if($invoice->reference)
                        <tr>
                            <td class="text-right" style="color: #66736C;">Reference / PO:</td>
                            <td class="text-right">{{ $invoice->reference }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-right" style="color: #66736C;">Status:</td>
                            <td class="text-right" style="text-transform: uppercase; font-weight: bold;">{{ $invoice->display_status }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td class="info-box">
                    <div class="box-title">Billed To</div>
                    <div style="font-weight: bold; font-size: 12px;">{{ $customer['name'] ?? 'Valued Customer' }}</div>
                    @if(!empty($customer['company_name']))
                        <div>{{ $customer['company_name'] }}</div>
                    @endif
                    @if(!empty($customer['billing_address']))
                        <div>{!! nl2br(e($customer['billing_address'])) !!}</div>
                    @endif
                    @if(!empty($customer['email']))
                        <div>Email: {{ $customer['email'] }}</div>
                    @endif
                    @if(!empty($customer['tax_number']))
                        <div>Tax ID: {{ $customer['tax_number'] }}</div>
                    @endif
                </td>
                <td style="width: 4%;"></td>
                <td class="info-box">
                    <div class="box-title">Invoice Information</div>
                    <div>Pricing Mode: <strong>{{ ucfirst($invoice->pricing_mode) }} Tax</strong></div>
                    <div>Currency: <strong>{{ $invoice->currency_code }} ({{ $invoice->currency_symbol }})</strong></div>
                    @if($invoice->payment_terms_text)
                        <div>Terms: {{ $invoice->payment_terms_text }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Description</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 12%;">Unit Price</th>
                <th class="text-center" style="width: 10%;">Tax</th>
                <th class="text-right" style="width: 18%;">Amount ({{ $invoice->currency_symbol }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
            <tr>
                <td style="color: #66736C;">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: bold;">{{ $item->description }}</div>
                    @if(\App\Services\DecimalMath::comp($item->line_discount_amount, '0') > 0)
                        <div style="font-size: 9px; color: #66736C;">
                            Line discount: -{{ $invoice->currency_symbol }}{{ $item->line_discount_amount }}
                        </div>
                    @endif
                </td>
                <td class="text-center">{{ (float)$item->quantity }} {{ $item->unit }}</td>
                <td class="text-right">{{ $invoice->currency_symbol }}{{ $item->unit_price }}</td>
                <td class="text-center">{{ (float)$item->tax_rate }}%</td>
                <td class="text-right" style="font-weight: bold;">
                    {{ $invoice->currency_symbol }}{{ $item->line_total }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="bank-box">
                        <div class="bank-title">Bank Transfer Instructions</div>
                        <table class="bank-details">
                            @if(!empty($bank['bank_name']))
                            <tr>
                                <td style="width: 35%; color: #66736C;">Bank Name:</td>
                                <td style="font-weight: bold;">{{ $bank['bank_name'] }}</td>
                            </tr>
                            @endif
                            @if(!empty($bank['account_holder']))
                            <tr>
                                <td style="color: #66736C;">Account Holder:</td>
                                <td style="font-weight: bold;">{{ $bank['account_holder'] }}</td>
                            </tr>
                            @endif
                            @if(!empty($bank['account_number']))
                            <tr>
                                <td style="color: #66736C;">Account Number:</td>
                                <td style="font-weight: bold; font-family: monospace; font-size: 11px;">{{ $bank['account_number'] }}</td>
                            </tr>
                            @endif
                            @if(!empty($bank['swift_bic']))
                            <tr>
                                <td style="color: #66736C;">SWIFT / BIC:</td>
                                <td>{{ $bank['swift_bic'] }}</td>
                            </tr>
                            @endif
                            @if(!empty($bank['branch']))
                            <tr>
                                <td style="color: #66736C;">Branch:</td>
                                <td>{{ $bank['branch'] }}</td>
                            </tr>
                            @endif
                            @if(!empty($bank['payment_instructions']))
                            <tr>
                                <td colspan="2" style="padding-top: 4px; font-style: italic; color: #475569;">
                                    {{ $bank['payment_instructions'] }}
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table class="totals-table">
                        <tr>
                            <td style="color: #66736C;">Gross Subtotal:</td>
                            <td class="text-right">{{ $invoice->currency_symbol }}{{ $invoice->gross_subtotal }}</td>
                        </tr>
                        @if(\App\Services\DecimalMath::comp($invoice->discount_total, '0') > 0)
                        <tr>
                            <td style="color: #66736C;">Total Discounts:</td>
                            <td class="text-right" style="color: #DC2626;">-{{ $invoice->currency_symbol }}{{ $invoice->discount_total }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="color: #66736C;">Taxable Base:</td>
                            <td class="text-right">{{ $invoice->currency_symbol }}{{ $invoice->taxable_subtotal }}</td>
                        </tr>
                        <tr>
                            <td style="color: #66736C;">Tax Amount:</td>
                            <td class="text-right">{{ $invoice->currency_symbol }}{{ $invoice->tax_total }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>Total ({{ $invoice->currency_code }}):</td>
                            <td class="text-right">{{ $invoice->currency_symbol }}{{ $invoice->total }}</td>
                        </tr>
                        @if(\App\Services\DecimalMath::comp($invoice->amount_paid, '0') > 0)
                        <tr>
                            <td style="color: #059669;">Amount Paid:</td>
                            <td class="text-right" style="color: #059669;">-{{ $invoice->currency_symbol }}{{ $invoice->amount_paid }}</td>
                        </tr>
                        @endif
                        <tr class="balance-due">
                            <td>Balance Due:</td>
                            <td class="text-right">{{ $invoice->currency_symbol }}{{ $invoice->outstanding_balance }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
        <div class="notes-section">
            <strong style="color: #17211D;">Notes / Terms:</strong>
            <p style="margin: 3px 0 0 0;">{!! nl2br(e($invoice->notes)) !!}</p>
        </div>
    @endif

    <div class="footer">
        Generated by SoloLedger on {{ now()->format('Y-m-d H:i') }} UTC. Page 1 of 1
    </div>

</body>
</html>
