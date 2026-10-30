<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BusinessSetting::current();
        $query = Payment::with(['invoice.customer', 'recordedBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%")
                         ->orWhereHas('customer', function ($cq) use ($search) {
                             $cq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($method = $request->input('method')) {
            $query->where('method', $method);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->where('payment_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->where('payment_date', '<=', $toDate);
        }

        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
            'search' => $search,
            'currentStatus' => $status,
            'currentMethod' => $method,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'currencySymbol' => $settings->currency_symbol,
        ]);
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', 'in:bank_transfer,cash,card,check,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            PaymentService::record($invoice, $validated, auth()->user());
            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Payment of {$validated['amount']} recorded successfully.");
        } catch (InvalidArgumentException $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', $e->getMessage());
        }
    }

    public function reverse(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'reversal_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        try {
            PaymentService::reverse($payment, $validated['reversal_reason'], auth()->user());
            return redirect()->back()
                ->with('success', "Payment #{$payment->id} has been reversed.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
