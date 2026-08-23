<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        // withoutGlobalScopes() so all branches' customers are visible to any branch admin
        $customers = Customer::withoutGlobalScopes()->with('branch')->orderBy('name', 'asc')->get();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $exists = Customer::where('phone', $request->phone)->first();
        if ($exists) {
            if ($request->expectsJson() || $request->input('source') === 'pos') {
                return response()->json(['success' => false, 'message' => 'You are already in our system. Please search and add!']);
            }

            return redirect()->back()->withErrors(['phone' => 'You are already in our system. Please search and add!'])->withInput();
        }

        $branchId = auth()->user()->branch_id ?? 1;

        $customer = Customer::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address ?? null,
            'is_active' => true,
        ]);

        // If JSON request (from POS modal), return JSON
        if ($request->expectsJson() || $request->input('source') === 'pos') {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return redirect()->back()->with('success', 'Customer added successfully.');
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');
        // withoutGlobalScopes() allows POS to search customers from any branch
        $query = Customer::withoutGlobalScopes()->orderBy('name');
        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%");
            });
        }

        return response()->json($query->limit(10)->get(['id', 'name', 'phone']));
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders.items.serviceType', 'orders' => fn ($q) => $q->latest()]);
        $totalSpend = $customer->orders->sum('total');
        $totalOrders = $customer->orders->count();

        return view('customers.show', compact('customer', 'totalSpend', 'totalOrders'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'phone', 'address']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized action.');
        }
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
