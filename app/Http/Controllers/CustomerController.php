<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CollectBulkCreditPaymentAction;
use App\Actions\Customer\StoreCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders')->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")->orWhere('title', 'like', "%{$s}%"));
        }

        if ($request->filled('type')) {
            if ($request->type === 'corporate') {
                $query->where(fn($q) => $q->where('customer_type', 'corporate')->orWhere('is_corporate', true));
            } elseif ($request->type === 'online') {
                $query->where('customer_type', 'online');
            } elseif ($request->type === 'walkin') {
                $query->where(fn($q) => $q->where('customer_type', 'walkin')->orWhere(fn($sub) => $sub->whereNull('customer_type')->where('is_corporate', false)));
            }
        }

        $customers = $query->paginate(15)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return redirect()->route('customers.index');
    }

    public function store(Request $request, StoreCustomerAction $action)
    {
        $request->validate([
            'title' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'nic' => 'nullable|string|max:20',
            'customer_mark' => 'nullable|string|max:255',
            'customer_type' => 'nullable|string|in:walkin,corporate,online',
            'is_corporate' => 'nullable|boolean',
        ]);

        if (Customer::where('phone', $request->phone)->exists()) {
            if ($request->expectsJson() || $request->input('source') === 'pos') {
                return response()->json(['success' => false, 'message' => 'You are already in our system. Please search and add!']);
            }
            return redirect()->back()->withErrors(['phone' => 'You are already in our system. Please search and add!'])->withInput();
        }

        $customer = $action->execute($request->all());

        if ($request->expectsJson() || $request->input('source') === 'pos') {
            return response()->json(['success' => true, 'customer' => $customer]);
        }
        return redirect()->back()->with('success', 'Customer added successfully.');
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');
        $query = Customer::withoutGlobalScopes()->orderBy('name');
        if ($q) {
            $query->where(fn($qb) => $qb->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }
        if ($request->input('with_packages', false)) {
            $query->with(['customerPackages' => fn($p) => $p->where('status', '!=', 'exhausted')->with(['package', 'services.serviceType'])]);
        }
        return response()->json($query->limit(20)->get(['id', 'name', 'phone', 'address', 'default_discount']));
    }

    public function show(Request $request, Customer $customer)
    {
        $tab = $request->input('tab', 'history');
        $totalOrders = $customer->orders()->count();
        $totalSpend = $customer->orders()->sum('total');
        $completedCount = $customer->orders()->where('status', 'delivered')->count();

        $pendingStats = $customer->orders()->where('payment_method', 'credit')->where('status', '!=', 'cancelled')
            ->whereColumn('paid_amount', '<', 'total')->selectRaw('COUNT(*) as count, SUM(total - paid_amount) as total')->first();
        $pendingCount = $pendingStats->count ?? 0;
        $totalPending = $pendingStats->total ?? 0;

        $historyOrders = null; $pendingOrdersList = null; $monthlySales = null; $salesOrders = null;
        $allPendingForBulk = ($pendingCount > 0) ? $customer->orders()->where('payment_method', 'credit')->where('status', '!=', 'cancelled')
            ->whereColumn('paid_amount', '<', 'total')->orderBy('created_at', 'asc')->get(['id', 'order_number', 'total', 'paid_amount'])
            ->map(fn($o) => ['id' => $o->id, 'number' => $o->order_number, 'due' => $o->total - $o->paid_amount])->values() : [];

        if ($tab === 'history') {
            $historyOrders = $customer->orders()->with('items.serviceType')->latest()->paginate(15)->withQueryString();
        } elseif ($tab === 'pending') {
            $pendingOrdersList = $customer->orders()->where('payment_method', 'credit')->where('status', '!=', 'cancelled')
                ->whereColumn('paid_amount', '<', 'total')->orderBy('created_at', 'asc')->paginate(15)->withQueryString();
        } elseif ($tab === 'sales') {
            $query = $customer->orders()->with('items.serviceType')->latest();
            if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
            if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);
            $salesOrders = $query->paginate(30)->withQueryString();
            $monthlySales = collect($salesOrders->items())->groupBy(fn($o) => $o->created_at->format('Y-m'))
                ->map(fn($orders, $ym) => [
                    'label' => Carbon::parse($ym . '-01')->format('F Y'),
                    'year_month' => $ym,
                    'order_count' => collect($orders)->count(),
                    'total_qty' => collect($orders)->sum(fn($o) => $o->items->sum('quantity')),
                    'total_bill' => collect($orders)->sum('total'),
                    'orders' => collect($orders)->sortByDesc('created_at')->values(),
                ]);
        }

        return view('customers.show', compact('customer', 'tab', 'totalSpend', 'totalOrders', 'completedCount', 'pendingCount', 'totalPending', 'allPendingForBulk', 'historyOrders', 'pendingOrdersList', 'monthlySales', 'salesOrders'));
    }

    public function salesReportPdf(Customer $customer, Request $request)
    {
        $yearMonth = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $query = $customer->orders()->with('items.serviceType')->latest();

        if ($yearMonth) {
            [$y, $m] = explode('-', $yearMonth);
            $query->whereYear('created_at', $y)->whereMonth('created_at', $m);
            $label = Carbon::parse($yearMonth . '-01')->format('F Y');
        } elseif ($startDate || $endDate) {
            if ($startDate) $query->whereDate('created_at', '>=', $startDate);
            if ($endDate) $query->whereDate('created_at', '<=', $endDate);
            $label = ($startDate ?: 'Start') . ' to ' . ($endDate ?: 'End');
        } else {
            $label = 'All Time';
        }

        $orders = $query->get();
        $totalQty = $orders->sum(fn($o) => $o->items->sum('quantity'));
        $totalBill = $orders->sum('total');

        return view('customers.sales_report_pdf', compact('customer', 'orders', 'label', 'totalQty', 'totalBill'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer, UpdateCustomerAction $action)
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
            'customer_type' => 'nullable|string|in:walkin,corporate,online',
            'is_corporate' => 'nullable|boolean',
        ]);

        $action->execute($customer, $request->all());
        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function bulkCreditPayment(Request $request, Customer $customer, CollectBulkCreditPaymentAction $action)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:cash,card,bank',
        ]);

        try {
            $result = $action->execute($customer, $request->only(['amount', 'payment_method']), auth()->id());
            $msg = "Successfully collected Rs. " . number_format($result['total_paid'], 2) . ". ";
            if (count($result['settled_orders']) > 0) $msg .= "Applied to " . count($result['settled_orders']) . " order(s). ";
            if ($result['unused_amount'] > 0) $msg .= "Note: Rs. " . number_format($result['unused_amount'], 2) . " was excess.";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Bulk Credit Payment Failed', ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('viewAny', Customer::class);
        try {
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Customer deleted.');
        } catch (QueryException $e) {
            return redirect()->route('customers.index')->withErrors(['error' => 'Cannot delete this customer because they have existing orders or records.']);
        }
    }
}
