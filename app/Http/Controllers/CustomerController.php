<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        
        $query = \App\Models\Customer::with('branch')->orderBy('name', 'asc');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $customers = $query->get();
        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $branchId = auth()->user()->branch_id ?? 1;

        $customer = \App\Models\Customer::create([
            'branch_id' => $branchId,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'address'   => $request->address ?? null,
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
        $branchId = auth()->user()->branch_id;
        $q = $request->input('q', '');
        $query = \App\Models\Customer::orderBy('name');
        if ($branchId) $query->where('branch_id', $branchId);
        if ($q) $query->where(function($qb) use ($q) {
            $qb->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%");
        });
        return response()->json($query->limit(10)->get(['id','name','phone']));
    }

    public function show(\App\Models\Customer $customer)
    {
        $customer->load(['orders.items.serviceType', 'orders' => fn($q) => $q->latest()]);
        $totalSpend  = $customer->orders->sum('total');
        $totalOrders = $customer->orders->count();
        return view('customers.show', compact('customer', 'totalSpend', 'totalOrders'));
    }

    public function edit(\App\Models\Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, \App\Models\Customer $customer)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);
        $customer->update($request->only(['name', 'phone', 'address']));
        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(\App\Models\Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
