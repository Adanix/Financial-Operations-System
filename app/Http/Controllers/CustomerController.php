<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('active') && $request->input('active') !== '') {
            $query->where('is_active', $request->boolean('active'));
        }

        $customers = $query->withCount('invoices')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'search' => $search,
            'activeFilter' => $request->input('active'),
        ]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'billing_address' => ['required', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $customer = Customer::create($validated);

        ActivityLogService::log(
            'customer.created',
            "Customer '{$customer->name}' was created.",
            $customer
        );

        if ($request->wantsJson()) {
            return response()->json(['customer' => $customer], 201);
        }

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $invoices = $customer->invoices()
            ->orderBy('issue_date', 'desc')
            ->paginate(10);

        return view('customers.show', [
            'customer' => $customer,
            'invoices' => $invoices,
            'lifetimeBilled' => $customer->lifetime_billed,
            'outstandingBalance' => $customer->outstanding_balance,
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'billing_address' => ['required', 'string'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $customer->update($validated);

        ActivityLogService::log(
            'customer.updated',
            "Customer '{$customer->name}' was updated.",
            $customer
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->invoices()->exists()) {
            // Cannot hard delete, archive instead
            $customer->update(['is_active' => false]);
            ActivityLogService::log(
                'customer.archived',
                "Customer '{$customer->name}' was deactivated (has existing invoices).",
                $customer
            );
            return redirect()->route('customers.index')->with('info', "Customer has existing invoices and was deactivated instead of deleted.");
        }

        $name = $customer->name;
        $customer->delete();

        ActivityLogService::log(
            'customer.deleted',
            "Customer '{$name}' was deleted.",
            null
        );

        return redirect()->route('customers.index')->with('success', "Customer '{$name}' was deleted.");
    }
}
