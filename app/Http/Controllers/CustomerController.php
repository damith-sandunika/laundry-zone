<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('orders')->orderBy('name', 'asc')->get();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $exists = Customer::where('phone', $request->phone)->first();
        if ($exists) {
            if ($request->expectsJson() || $request->input('source') === 'pos') {
                return response()->json(['success' => false, 'message' => 'You are already in our system. Please search and add!']);
            }

            return redirect()->back()->withErrors(['phone' => 'You are already in our system. Please search and add!'])->withInput();
        }

        $customer = Customer::create([
            'title' => $request->title,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
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
        // Search all customers (no branch restriction) so any branch can serve any customer
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
            'title' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->only(['title', 'name', 'phone', 'email', 'address']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if (! in_array(auth()->user()->role, ['admin', 'super_admin', 'branch_admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Customer deleted.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('customers.index')->withErrors(['error' => 'Cannot delete this customer because they have existing orders or records.']);
        }
    }
}
