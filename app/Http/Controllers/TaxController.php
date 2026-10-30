<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\TaxRate;
use App\Services\ActivityLogService;
use App\Services\ReportingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(Request $request): View
    {
        $settings = BusinessSetting::current();
        $preset = $request->input('period', 'this_month');
        $range = ReportingService::parseDateRange(
            $preset,
            $request->input('from_date'),
            $request->input('to_date'),
            $settings->timezone
        );

        $taxSummary = ReportingService::getTaxSummary($range);

        $taxRates = TaxRate::withCount(['expenses', 'invoiceItems'])
            ->orderBy('rate')
            ->get();

        return view('taxes.index', [
            'summary' => $taxSummary,
            'taxRates' => $taxRates,
            'currentPeriod' => $preset,
            'range' => $range,
            'currencySymbol' => $settings->currency_symbol,
            'taxLabel' => $settings->tax_label,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'rate' => ['required', 'numeric', 'between:0,100'],
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($validated, $isDefault) {
            if ($isDefault) {
                TaxRate::where('is_default', true)->update(['is_default' => false]);
            }

            $rate = TaxRate::create([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'rate' => $validated['rate'],
                'description' => $validated['description'] ?? null,
                'is_default' => $isDefault,
                'is_active' => true,
            ]);

            ActivityLogService::log(
                'tax_rate.created',
                "Tax rate '{$rate->name}' ({$rate->rate}%) was created.",
                $rate
            );
        });

        return redirect()->route('taxes.index')->with('success', 'Tax rate created successfully.');
    }

    public function update(Request $request, TaxRate $taxRate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'rate' => ['required', 'numeric', 'between:0,100'],
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($taxRate, $validated, $isDefault, $request) {
            if ($isDefault) {
                TaxRate::where('id', '!=', $taxRate->id)->where('is_default', true)->update(['is_default' => false]);
            }

            $taxRate->update([
                'name' => $validated['name'],
                'code' => $validated['code'] ?? null,
                'rate' => $validated['rate'],
                'description' => $validated['description'] ?? null,
                'is_default' => $isDefault,
                'is_active' => $request->boolean('is_active', true),
            ]);

            ActivityLogService::log(
                'tax_rate.updated',
                "Tax rate '{$taxRate->name}' was updated.",
                $taxRate
            );
        });

        return redirect()->route('taxes.index')->with('success', 'Tax rate updated.');
    }

    public function setDefault(TaxRate $taxRate): RedirectResponse
    {
        if (!$taxRate->is_active) {
            return redirect()->back()->with('error', 'Inactive tax rate cannot be set as default.');
        }

        DB::transaction(function () use ($taxRate) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
            $taxRate->update(['is_default' => true]);

            ActivityLogService::log(
                'tax_rate.default',
                "Tax rate '{$taxRate->name}' set as default.",
                $taxRate
            );
        });

        return redirect()->route('taxes.index')->with('success', "'{$taxRate->name}' is now the default tax rate.");
    }

    public function toggleStatus(TaxRate $taxRate): RedirectResponse
    {
        $newStatus = !$taxRate->is_active;

        if (!$newStatus && $taxRate->is_default) {
            return redirect()->back()->with('error', 'Cannot deactivate the default tax rate. Assign a new default first.');
        }

        $taxRate->update(['is_active' => $newStatus]);

        ActivityLogService::log(
            'tax_rate.status',
            "Tax rate '{$taxRate->name}' was " . ($newStatus ? 'activated' : 'deactivated') . '.',
            $taxRate
        );

        return redirect()->route('taxes.index')->with('success', "Tax rate is now " . ($newStatus ? 'active' : 'inactive') . ".");
    }
}
