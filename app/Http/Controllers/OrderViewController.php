<?php

namespace App\Http\Controllers;

use App\Models\Cubby;
use App\Models\Extra;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\TurnaroundType;
use Illuminate\Http\Request;

class OrderViewController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Super admins use the unified history page to view and manage all orders across branches
        if ($user->isSuperAdmin()) {
            return redirect()->route('orders.history');
        }

        // Workshop admins don't use the branch orders page — redirect to their operations page
        if ($user->role === 'workshop_admin') {
            return redirect()->route('workshop.operations');
        }

        $branchId = $user->branch_id;
        $q = $request->input('q');
        $status = $request->input('status');
        $isOnline = $request->input('is_online');
        $paymentReady = $request->input('payment_ready');

        $filterBranchId = $request->input('branch_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $tab = $request->input('tab', 'received');

        // Calculate counts for tabs
        $baseQuery = Order::query();
        if ($branchId && !\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            $baseQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhere('workshop_id', $branchId);
            });
        } elseif (\Illuminate\Support\Facades\Gate::allows('is-super-admin') && $filterBranchId) {
            $baseQuery->where('branch_id', $filterBranchId);
        }

        // Single grouped COUNT query — replaces separate round-trips
        $statusCountsRaw = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $counts = [
            'received'   => ($statusCountsRaw['received'] ?? 0) + ($statusCountsRaw['picked_up'] ?? 0) + ($statusCountsRaw['dispatched_to_workshop'] ?? 0),
            'processing' => ($statusCountsRaw['processing'] ?? 0) + ($statusCountsRaw['completed_at_workshop'] ?? 0),
            'arriving'   => $statusCountsRaw['returned_from_workshop'] ?? 0,
            'done'       => $statusCountsRaw['done'] ?? 0,
            'delivered'  => ($statusCountsRaw['delivered'] ?? 0) + ($statusCountsRaw['completed'] ?? 0),
        ];

        $query = Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop', 'deliveryAppointment', 'paymentTransactions'])
            ->orderBy('created_at', 'desc');

        if ($branchId && !\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhere('workshop_id', $branchId);
            });
        } elseif (\Illuminate\Support\Facades\Gate::allows('is-super-admin') && $filterBranchId) {
            $query->where('branch_id', $filterBranchId);
        }

        if ($status) {
            $query->where('status', $status);
        } elseif (!$q) {
            if ($tab === 'received') {
                $query->whereIn('status', ['received', 'picked_up', 'dispatched_to_workshop']);
            } elseif ($tab === 'processing') {
                $query->whereIn('status', ['processing', 'completed_at_workshop']);
            } elseif ($tab === 'arriving') {
                $query->where('status', 'returned_from_workshop');
            } elseif ($tab === 'done') {
                $query->where('status', 'done');
            } elseif ($tab === 'delivered') {
                $query->whereIn('status', ['delivered', 'completed']);
            }
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($isOnline) {
            $query->where('is_online_booking', true);
        }
        if ($paymentReady) {
            $query->where(function ($q) {
                $q->where('payment_approved', true)
                    ->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0');
            });
        }

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('order_number', 'like', "%{$q}%")
                   ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%"));
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        $orderIds = $orders->pluck('id')->toArray();
        $dispatches = \App\Models\WorkshopDispatch::whereIn('order_id', $orderIds)
            ->whereIn('status', ['returned_to_branch', 'received_at_branch'])
            ->orderByDesc('returned_at')
            ->get()
            ->groupBy('order_id');

        $orders->getCollection()->transform(function ($order) use ($dispatches) {
            $dispatch = $dispatches->has($order->id) ? $dispatches->get($order->id)->first() : null;
            $order->workshop_dispatch_id = $dispatch ? $dispatch->id : null;
            return $order;
        });

        $cubbies = Cubby::where('branch_id', $this->getActiveBranchId())->orderBy('name')->get();
        $categories = ServiceCategory::with('services')->where('is_active', true)->get();
        $workshops = \App\Models\Branch::workshops()->get();
        $minimumDeliveryCost = Setting::get('minimum_delivery_cost', 0);
        $allBranches = \Illuminate\Support\Facades\Gate::allows('is-super-admin') ? \App\Models\Branch::all() : collect();

        return view('orders.index', compact('orders', 'cubbies', 'categories', 'workshops', 'minimumDeliveryCost', 'allBranches', 'filterBranchId', 'tab', 'counts'));
    }

    public function edit(Order $order)
    {
        $order->load('items.serviceType', 'items.extras.extra', 'customer');
        $branchId = $this->getActiveBranchId();
        $isCentralized = Setting::get('centralized_management', '1') == '1';

        $services = ServiceType::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();

        $categories = ServiceCategory::with([
            'children' => function ($q) use ($branchId, $isCentralized) {
                $q->where('is_active', true);
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
                $q->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)
        ->where(function($q) use ($branchId, $isCentralized) {
            if (!$isCentralized && $branchId) {
                $q->where('branch_id', $branchId);
            }
        })->orderBy('order_index')->get();

        $cartItems = $order->items->map(function ($item) {
            return [
                'service_type_id' => $item->service_type_id,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'base_price' => $item->unit_price,
                'qty' => $item->quantity,
                'turnaround_type_id' => (float)$item->custom_turnaround_percentage > 0 ? 'custom' : $item->turnaround_type_id,
                'custom_turnaround_percentage' => $item->custom_turnaround_percentage,
                'selected_extras' => $item->extras->map(function ($extra) {
                    return [
                        'id' => $extra->extra_id,
                        'name' => $extra->extra ? $extra->extra->name : 'Extra',
                        'price' => $extra->unit_price,
                        'qty' => $extra->quantity,
                        'total' => $extra->total_price,
                    ];
                })->toArray(),
                'note' => $item->note,
            ];
        })->toArray();

        $minimumDeliveryCost = Setting::get('minimum_delivery_cost', 0);
        $dispatch = (object) ['order' => $order, 'order_id' => $order->id];

        $turnaroundTypes = TurnaroundType::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();

        $systemExtras = Extra::where('is_active', true)
            ->where(function($q) use ($branchId, $isCentralized) {
                if (!$isCentralized && $branchId) {
                    $q->where('branch_id', $branchId);
                }
            })->get();

        return view('orders.edit', compact('order', 'dispatch', 'services', 'categories', 'cartItems', 'minimumDeliveryCost', 'turnaroundTypes', 'systemExtras'));
    }

    public function showByNumber($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->load([
            'customer', 
            'items.serviceType.category', 
            'items.turnaroundType', 
            'items.extras.extra', 
            'statusHistory.changedBy', 
            'cubbies.cubby', 
            'createdBy', 
            'deliveryAppointment', 
            'correctionRequests.requestedBy', 
            'correctionRequests.approvedBy'
        ]);

        $branchId = $this->getActiveBranchId();
        $cubbies = Cubby::where('branch_id', $branchId)->orderBy('name')->get();

        $pickmeCost = \App\Models\Expense::where('category', 'PickMe')
            ->where(function ($q) use ($order) {
                $q->where('description', 'like', '%Order #' . $order->order_number . '%');
                if ($order->delivery_appointment_id) {
                    $q->orWhere('description', 'like', '%pickup delivery #' . $order->delivery_appointment_id . '%')
                        ->orWhere('description', 'like', '%delivery dropoff #' . $order->delivery_appointment_id . '%');
                }
            })->sum('amount');

        $categories = ServiceCategory::with([
            'services' => function ($q) {
                $q->where('is_active', true);
            }
        ])->orderBy('name')->get();

        $allServiceTypes = ServiceType::with(['category.parent'])->get();
        $serviceTypesMap = $allServiceTypes->mapWithKeys(function ($st) {
            $catName = null;
            $subCatName = null;
            if ($st->category) {
                if ($st->category->parent) {
                    $catName = $st->category->parent->name;
                    $subCatName = $st->category->name;
                } else {
                    $catName = $st->category->name;
                }
            }
            return [$st->id => [
                'name' => $st->name,
                'category' => $catName,
                'subcategory' => $subCatName,
            ]];
        })->toArray();

        return view('orders.show', compact('order', 'cubbies', 'pickmeCost', 'categories', 'serviceTypesMap'));
    }
}
