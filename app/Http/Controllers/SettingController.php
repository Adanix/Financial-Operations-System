<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BusinessSetting;
use App\Models\ExpenseCategory;
use App\Models\TaxRate;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BusinessSetting::current();
        $tab = $request->input('tab', 'profile');
        $allowedTabs = ['profile', 'currency', 'bank', 'invoicing', 'categories', 'audit'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'profile';
        }

        $taxRates = TaxRate::where('is_active', true)->orderBy('rate')->get();
        $categories = ExpenseCategory::withCount('expenses')->orderBy('name')->get();
        $auditLogs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(20);

        return view('settings.index', [
            'settings' => $settings,
            'tab' => $tab,
            'taxRates' => $taxRates,
            'categories' => $categories,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        $settings = BusinessSetting::current();

        if ($section === 'profile') {
            $validated = $request->validate([
                'business_name' => ['required', 'string', 'max:255'],
                'legal_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'website' => ['nullable', 'url', 'max:255'],
                'address' => ['nullable', 'string'],
                'tax_number' => ['nullable', 'string', 'max:50'],
            ]);
            $settings->update($validated);
        } elseif ($section === 'currency') {
            $validated = $request->validate([
                'currency_code' => ['required', 'string', 'max:10'],
                'currency_symbol' => ['required', 'string', 'max:10'],
                'currency_decimals' => ['required', 'integer', 'in:0,2'],
                'timezone' => ['required', 'timezone'],
                'date_format' => ['required', 'string', 'max:20'],
                'tax_label' => ['required', 'string', 'max:20'],
            ]);
            $settings->update($validated);
        } elseif ($section === 'bank') {
            $validated = $request->validate([
                'bank_name' => ['required', 'string', 'max:255'],
                'account_holder' => ['required', 'string', 'max:255'],
                'account_number' => ['required', 'string', 'max:100'],
                'swift_bic' => ['nullable', 'string', 'max:50'],
                'branch' => ['nullable', 'string', 'max:255'],
                'payment_instructions' => ['nullable', 'string'],
            ]);
            $settings->update($validated);
        } elseif ($section === 'invoicing') {
            $validated = $request->validate([
                'invoice_prefix' => ['required', 'string', 'max:10'],
                'sequence_padding' => ['required', 'integer', 'between:2,8'],
                'default_payment_terms' => ['required', 'integer', 'between:0,180'],
                'default_pricing_mode' => ['required', 'in:exclusive,inclusive'],
                'default_tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
                'default_notes' => ['nullable', 'string'],
            ]);
            $settings->update($validated);
        }

        ActivityLogService::log(
            'settings.updated',
            "Settings section '{$section}' was updated.",
            $settings
        );

        return redirect()->route('settings.index', ['tab' => $section])
            ->with('success', 'Settings updated successfully.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $cat = ExpenseCategory::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        ActivityLogService::log(
            'category.created',
            "Expense category '{$cat->name}' was created.",
            $cat
        );

        return redirect()->route('settings.index', ['tab' => 'categories'])
            ->with('success', "Category '{$cat->name}' created.");
    }

    public function updateCategory(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name,' . $category->id],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogService::log(
            'category.updated',
            "Expense category '{$category->name}' was updated.",
            $category
        );

        return redirect()->route('settings.index', ['tab' => 'categories'])
            ->with('success', "Category updated.");
    }
}
