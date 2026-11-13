<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cubby;
use App\Models\Expense;
use App\Models\Extra;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\TurnaroundType;
use App\Models\WorkshopDispatch;
use App\Services\OrderQueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderViewController extends Controller
{
    protected OrderQueryFilterService $filterService;

    public function __construct(OrderQueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) return redirect()->route('orders.history');
        if ($user->role === 'workshop_admin') return redirect()->route('workshop.operations');

        $branchId = $user->branch_id;
        $filterBranchId = $request->input('branch_id');
        $tab = $request->input('tab', 'received');

        $baseQuery = Order::query();
        if ($branchId && !Gate::allows('is-super-admin')) {
            $baseQuery->where(fn($q) => $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId));
        } elseif (Gate::allows('is-super-admin') && $filterBranchId) {
            $baseQuery->where('branch_id', $filterBranchId);
        }

        $counts = $this->filterService->getCounts($baseQuery);

        $query = Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop', 'deliveryAppointment', 'paymentTransactions'])
            ->orderBy('created_at', 'desc');

        if ($branchId && !Gate::allows('is-super-admin')) {
            $query->where(fn($q) => $q->where('branch_id', $branchId)->orWhere('workshop_id', $branchId));
        } elseif (Gate::allows('is-super-admin') && $filterBranchId) {
            $query->where('branch_id', $filterBranchId);
        }

        $query = $this->filterService->applyFilters($query, $request->all());
        $orders = $query->paginate(15)->withQueryString();

        $orderIds = $orders->pluck('id')->toArray();
        $dispatches = WorkshopDispatch::whereIn('order_id', $orderIds)
            ->whereIn('status', ['returned_to_branch', 'received_at_branch'])
            ->orderByDesc('returned_at')->get()->groupBy('order_id');

        $orders->getCollection()->transform(function ($order) use ($dispatches) {
            $dispatch = $dispatches->get($order->id)?->first();
            $order->workshop_dispatch_id = $dispatch?->id;
            return $order;
        });

        $cubbies = Cubby::where('branch_id', $this->getActiveBranchId())->orderBy('name')->get();
        $categories = ServiceCategory::with('services')->where('is_active', true)->get();
        $workshops = Branch::workshops()->get();
        $minimumDeliveryCost = Setting::get('minimum_delivery_cost', 0);
        $allBranches = Gate::allows('is-super-admin') ? Branch::all() : collect();

        return view('orders.index', compact('orders', 'cubbies', 'categories', 'workshops', 'minimumDeliveryCost', 'allBranches', 'filterBranchId', 'tab', 'counts'));
    }

    public function edit(Order $order)
    {
        $order->load('items.serviceType', 'items.extras.extra', 'customer');
        $branchId = $this->getActiveBranchId();
        $isCentralized = Setting::get('centralized_management', '1') == '1';

        $branchScope = fn($q) => (!$isCentralized && $branchId) ? $q->where('branch_id', $branchId) : null;

        $services = ServiceType::where('is_active', true)->where($branchScope)->get();
        $categories = ServiceCategory::with(['children' => fn($q) => $q->where('is_active', true)->where($branchScope)->orderBy('order_index')])
            ->whereNull('parent_id')->where('is_active', true)->where($branchScope)->orderBy('order_index')->get();

        $cartItems = $order->items->map(fn($item) => [
            'service_type_id' => $item->service_type_id,
            'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
            'base_price' => $item->unit_price, 'qty' => $item->quantity,
            'turnaround_type_id' => (float)$item->custom_turnaround_percentage > 0 ? 'custom' : $item->turnaround_type_id,
            'custom_turnaround_percentage' => $item->custom_turnaround_percentage,
            'selected_extras' => $item->extras->map(fn($ex) => ['id' => $ex->extra_id, 'name' => $ex->extra?->name ?? 'Extra', 'price' => $ex->unit_price, 'qty' => $ex->quantity, 'total' => $ex->total_price])->toArray(),
            'note' => $item->note,
        ])->toArray();

        $minimumDeliveryCost = Setting::get('minimum_delivery_cost', 0);
        $dispatch = (object) ['order' => $order, 'order_id' => $order->id];
        $turnaroundTypes = TurnaroundType::where('is_active', true)->where($branchScope)->get();
        $systemExtras = Extra::where('is_active', true)->where($branchScope)->get();

        return view('orders.edit', compact('order', 'dispatch', 'services', 'categories', 'cartItems', 'minimumDeliveryCost', 'turnaroundTypes', 'systemExtras'));
    }

    public function showByNumber($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->load(['customer', 'items.serviceType.category', 'items.turnaroundType', 'items.extras.extra', 'statusHistory.changedBy', 'cubbies.cubby', 'createdBy', 'deliveryAppointment', 'correctionRequests.requestedBy', 'correctionRequests.approvedBy']);

        $cubbies = Cubby::where('branch_id', $this->getActiveBranchId())->orderBy('name')->get();

        $pickmeCost = Expense::where('category', 'PickMe')->where(function ($q) use ($order) {
            $q->where('description', 'like', '%Order #' . $order->order_number . '%');
            if ($order->delivery_appointment_id) {
                $q->orWhere('description', 'like', '%pickup delivery #' . $order->delivery_appointment_id . '%')
                  ->orWhere('description', 'like', '%delivery dropoff #' . $order->delivery_appointment_id . '%');
            }
        })->sum('amount');

        $categories = ServiceCategory::with(['services' => fn($q) => $q->where('is_active', true)])->orderBy('name')->get();
        $serviceTypesMap = ServiceType::with(['category.parent'])->get()->mapWithKeys(fn($st) => [
            $st->id => [
                'name' => $st->name,
                'category' => $st->category?->parent ? $st->category->parent->name : $st->category?->name,
                'subcategory' => $st->category?->parent ? $st->category->name : null,
            ]
        ])->toArray();

        return view('orders.show', compact('order', 'cubbies', 'pickmeCost', 'categories', 'serviceTypesMap'));
    }
}
