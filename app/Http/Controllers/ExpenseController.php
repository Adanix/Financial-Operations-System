<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\TaxRate;
use App\Services\ActivityLogService;
use App\Services\ExpenseCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BusinessSetting::current();
        $query = Expense::with(['category', 'taxRate']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('vendor', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($catId = $request->input('category_id')) {
            $query->where('expense_category_id', $catId);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->where('expense_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->where('expense_date', '<=', $toDate);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'search' => $search,
            'currentCategory' => $catId,
            'currentPaymentStatus' => $paymentStatus,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'currencySymbol' => $settings->currency_symbol,
        ]);
    }

    public function create(): View
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $taxRates = TaxRate::where('is_active', true)->orderBy('rate')->get();

        return view('expenses.create', [
            'categories' => $categories,
            'taxRates' => $taxRates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date'],
            'vendor' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'entered_amount' => ['required', 'numeric', 'gt:0'],
            'pricing_mode' => ['required', 'in:exclusive,inclusive'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'tax_recoverable' => ['boolean'],
            'payment_status' => ['required', 'in:paid,unpaid'],
            'payment_date' => ['nullable', 'required_if:payment_status,paid', 'date'],
            'payment_method' => ['nullable', 'required_if:payment_status,paid', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $taxRateModel = !empty($validated['tax_rate_id']) ? TaxRate::find($validated['tax_rate_id']) : null;
        $taxRate = $taxRateModel ? (string)$taxRateModel->rate : '0.0000';
        $taxName = $taxRateModel ? $taxRateModel->name : null;
        $taxRecoverable = $request->boolean('tax_recoverable');

        $calc = ExpenseCalculator::calculate(
            $validated['entered_amount'],
            $taxRate,
            $validated['pricing_mode'],
            $taxRecoverable,
            2
        );

        $receiptPath = null;
        $receiptOriginalName = null;

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $receiptOriginalName = $file->getClientOriginalName();
            $receiptPath = $file->store('receipts', 'local');
        }

        $expense = Expense::create([
            'expense_category_id' => $validated['expense_category_id'],
            'expense_date' => $validated['expense_date'],
            'vendor' => $validated['vendor'],
            'description' => $validated['description'],
            'entered_amount' => $calc['entered_amount'],
            'pricing_mode' => $validated['pricing_mode'],
            'tax_rate_id' => $taxRateModel?->id,
            'tax_name' => $taxName,
            'tax_rate' => $taxRate,
            'tax_recoverable' => $taxRecoverable,
            'net_amount' => $calc['net_amount'],
            'tax_amount' => $calc['tax_amount'],
            'gross_amount' => $calc['gross_amount'],
            'payment_status' => $validated['payment_status'],
            'payment_date' => ($validated['payment_status'] === 'paid') ? $validated['payment_date'] : null,
            'payment_method' => ($validated['payment_status'] === 'paid') ? $validated['payment_method'] : null,
            'reference' => $validated['reference'] ?? null,
            'receipt_path' => $receiptPath,
            'receipt_original_name' => $receiptOriginalName,
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLogService::log(
            'expense.created',
            "Expense of {$expense->gross_amount} to '{$expense->vendor}' recorded.",
            $expense
        );

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['category', 'taxRate']);
        $settings = BusinessSetting::current();

        return view('expenses.show', [
            'expense' => $expense,
            'settings' => $settings,
        ]);
    }

    public function edit(Expense $expense): View
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $taxRates = TaxRate::where('is_active', true)->orderBy('rate')->get();

        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => $categories,
            'taxRates' => $taxRates,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date'],
            'vendor' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'entered_amount' => ['required', 'numeric', 'gt:0'],
            'pricing_mode' => ['required', 'in:exclusive,inclusive'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'tax_recoverable' => ['boolean'],
            'payment_status' => ['required', 'in:paid,unpaid'],
            'payment_date' => ['nullable', 'required_if:payment_status,paid', 'date'],
            'payment_method' => ['nullable', 'required_if:payment_status,paid', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $taxRateModel = !empty($validated['tax_rate_id']) ? TaxRate::find($validated['tax_rate_id']) : null;
        $taxRate = $taxRateModel ? (string)$taxRateModel->rate : '0.0000';
        $taxName = $taxRateModel ? $taxRateModel->name : null;
        $taxRecoverable = $request->boolean('tax_recoverable');

        $calc = ExpenseCalculator::calculate(
            $validated['entered_amount'],
            $taxRate,
            $validated['pricing_mode'],
            $taxRecoverable,
            2
        );

        $receiptPath = $expense->receipt_path;
        $receiptOriginalName = $expense->receipt_original_name;

        if ($request->hasFile('receipt')) {
            // Delete old receipt
            if ($expense->receipt_path && Storage::disk('local')->exists($expense->receipt_path)) {
                Storage::disk('local')->delete($expense->receipt_path);
            }
            $file = $request->file('receipt');
            $receiptOriginalName = $file->getClientOriginalName();
            $receiptPath = $file->store('receipts', 'local');
        }

        $expense->update([
            'expense_category_id' => $validated['expense_category_id'],
            'expense_date' => $validated['expense_date'],
            'vendor' => $validated['vendor'],
            'description' => $validated['description'],
            'entered_amount' => $calc['entered_amount'],
            'pricing_mode' => $validated['pricing_mode'],
            'tax_rate_id' => $taxRateModel?->id,
            'tax_name' => $taxName,
            'tax_rate' => $taxRate,
            'tax_recoverable' => $taxRecoverable,
            'net_amount' => $calc['net_amount'],
            'tax_amount' => $calc['tax_amount'],
            'gross_amount' => $calc['gross_amount'],
            'payment_status' => $validated['payment_status'],
            'payment_date' => ($validated['payment_status'] === 'paid') ? $validated['payment_date'] : null,
            'payment_method' => ($validated['payment_status'] === 'paid') ? $validated['payment_method'] : null,
            'reference' => $validated['reference'] ?? null,
            'receipt_path' => $receiptPath,
            'receipt_original_name' => $receiptOriginalName,
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLogService::log(
            'expense.updated',
            "Expense #{$expense->id} to '{$expense->vendor}' updated.",
            $expense
        );

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $id = $expense->id;
        $vendor = $expense->vendor;

        if ($expense->receipt_path && Storage::disk('local')->exists($expense->receipt_path)) {
            Storage::disk('local')->delete($expense->receipt_path);
        }

        $expense->delete();

        ActivityLogService::log(
            'expense.deleted',
            "Expense #{$id} to '{$vendor}' was deleted."
        );

        return redirect()->route('expenses.index')->with('success', "Expense #{$id} was deleted.");
    }

    public function receipt(Expense $expense): StreamedResponse|BinaryFileResponse|RedirectResponse
    {
        if (!$expense->receipt_path || !Storage::disk('local')->exists($expense->receipt_path)) {
            return redirect()->back()->with('error', 'Receipt file not found.');
        }

        return Storage::disk('local')->download(
            $expense->receipt_path,
            $expense->receipt_original_name ?: "receipt_{$expense->id}"
        );
    }
}
