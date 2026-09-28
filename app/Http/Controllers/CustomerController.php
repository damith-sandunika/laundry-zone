<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders')->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('title', 'like', "%{$searchTerm}%");
            });
        }

        $customers = $query->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'nic' => 'nullable|string|max:20',
            'customer_mark' => 'nullable|string|max:255',
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
            'date_of_birth' => $request->date_of_birth ?? null,
            'nic' => $request->nic ?? null,
            'customer_mark' => $request->customer_mark ?? null,
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

        $withPackages = $request->input('with_packages', false);

        if ($withPackages) {
            $query->with(['customerPackages' => function ($q) {
                $q->where('status', '!=', 'exhausted')
                  ->with(['package', 'services.serviceType']);
            }]);
        }

        return response()->json($query->limit(20)->get(['id', 'name', 'phone', 'default_discount']));
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'orders' => fn($q) => $q->latest(),
            'orders.items.serviceType',
        ]);
        $totalSpend = $customer->orders->sum('total');
        $totalOrders = $customer->orders->count();

        // Build monthly sales report grouped by year-month descending
        $monthlySales = $customer->orders
            ->groupBy(fn($o) => $o->created_at->format('Y-m'))
            ->sortKeysDesc()
            ->map(function ($orders, $yearMonth) {
                $totalQty = $orders->sum(fn($o) => $o->items->sum('quantity'));
                return [
                    'label'       => \Carbon\Carbon::parse($yearMonth . '-01')->format('F Y'),
                    'year_month'  => $yearMonth,
                    'order_count' => $orders->count(),
                    'total_qty'   => $totalQty,
                    'total_bill'  => $orders->sum('total'),
                    'orders'      => $orders->sortByDesc('created_at')->values(),
                ];
            })->values();

        return view('customers.show', compact('customer', 'totalSpend', 'totalOrders', 'monthlySales'));
    }

    public function salesReportPdf(Customer $customer, \Illuminate\Http\Request $request)
    {
        $yearMonth = $request->input('month'); // e.g. "2026-09"

        $query = $customer->orders()->with('items.serviceType')->latest();
        if ($yearMonth) {
            [$y, $m] = explode('-', $yearMonth);
            $query->whereYear('created_at', $y)->whereMonth('created_at', $m);
        }
        $orders = $query->get();

        $label     = $yearMonth ? \Carbon\Carbon::parse($yearMonth . '-01')->format('F Y') : 'All Time';
        $totalQty  = $orders->sum(fn($o) => $o->items->sum('quantity'));
        $totalBill = $orders->sum('total');

        return view('customers.sales_report_pdf', compact(
            'customer', 'orders', 'label', 'totalQty', 'totalBill'
        ));
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
            'date_of_birth' => 'nullable|date',
            'nic' => 'nullable|string|max:20',
            'customer_mark' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['title', 'name', 'phone', 'email', 'address', 'date_of_birth', 'nic', 'customer_mark']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('viewAny', \App\Models\Customer::class);
        
        try {
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Customer deleted.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('customers.index')->withErrors(['error' => 'Cannot delete this customer because they have existing orders or records.']);
        }
    }
}

