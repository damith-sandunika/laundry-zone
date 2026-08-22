<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkshopController extends Controller
{
    // ─── Branch Admin: Dispatch order to workshop ───
    public function dispatch(Request $request, Order $order)
    {
        $request->validate([
            'dispatch_notes' => 'nullable|string|max:1000',
            'workshop_branch_id' => 'nullable|exists:branches,id',
        ]);

        // Check if already dispatched
        $existing = WorkshopDispatch::where('order_id', $order->id)
            ->whereNotIn('status', ['received_at_branch'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Order is already dispatched to workshop.');
        }

        $dispatch = WorkshopDispatch::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'workshop_branch_id' => $request->workshop_branch_id,
            'dispatched_by' => auth()->id(),
            'status' => 'dispatched_to_workshop',
            'dispatched_at' => now(),
            'dispatch_notes' => $request->dispatch_notes,
        ]);

        $order->update([
            'status' => 'dispatched_to_workshop',
            'dispatched_to_workshop_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Order dispatched to workshop successfully.');
    }

    public function bulkDispatch(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'workshop_branch_id' => 'required|exists:branches,id',
            'dispatch_notes' => 'nullable|string',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)
            ->whereNotIn('status', ['dispatched_to_workshop'])
            ->get();

        foreach ($orders as $order) {
            WorkshopDispatch::create([
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'workshop_branch_id' => $request->workshop_branch_id,
                'dispatched_by' => auth()->id(),
                'status' => 'dispatched_to_workshop',
                'dispatched_at' => now(),
                'dispatch_notes' => $request->dispatch_notes,
            ]);

            $order->update([
                'status' => 'dispatched_to_workshop',
                'dispatched_to_workshop_at' => now(),
                'workshop_id' => $request->workshop_branch_id,
            ]);
        }

        return redirect()->back()->with('success', count($orders).' orders dispatched to workshop.');
    }

    // ─── Workshop Admin: Dashboard ───
    public function dashboard()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $dispatches = WorkshopDispatch::with(['order.customer', 'branch', 'dispatchedBy'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereNotIn('status', ['received_at_branch'])
            ->orderByDesc('dispatched_at')
            ->get();

        $completedToday = WorkshopDispatch::where('status', 'received_at_branch')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereDate('received_at_branch_at', today())
            ->count();

        $branches = Branch::where('type', 'branch')->get();

        return view('workshop.dashboard', compact('dispatches', 'completedToday', 'branches'));
    }

    // ─── Workshop Admin: Mark received at workshop ───
    public function receive(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'received_at_workshop',
            'received_by' => auth()->id(),
            'received_at_workshop_at' => now(),
        ]);

        $dispatch->order->update(['status' => 'processing']);

        return response()->json(['success' => true, 'status' => 'received_at_workshop']);
    }

    // ─── Workshop Admin: Mark processing ───
    public function startProcessing(WorkshopDispatch $dispatch)
    {
        $dispatch->update(['status' => 'processing']);
        $dispatch->order->update(['status' => 'processing']);

        return response()->json(['success' => true, 'status' => 'processing']);
    }

    // ─── Workshop Admin: Mark completed at workshop ───
    public function complete(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'completed_at_workshop',
            'completed_at_workshop_at' => now(),
        ]);

        return response()->json(['success' => true, 'status' => 'completed_at_workshop']);
    }

    // ─── Workshop Admin: Return to branch ───
    public function returnToBranch(WorkshopDispatch $dispatch)
    {
        $dispatch->update([
            'status' => 'returned_to_branch',
            'returned_by' => auth()->id(),
            'returned_at' => now(),
        ]);

        $dispatch->order->update(['status' => 'returned_from_workshop']);

        return response()->json(['success' => true, 'status' => 'returned_to_branch']);
    }

    // ─── Workshop Admin: Bulk Status Update ───
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'dispatch_ids' => 'required|array',
            'dispatch_ids.*' => 'exists:workshop_dispatches,id',
            'status' => 'required|string|in:processing,completed_at_workshop,returned_to_branch',
        ]);

        $status = $request->status;
        $dispatchIds = $request->dispatch_ids;

        DB::transaction(function () use ($dispatchIds, $status) {
            foreach ($dispatchIds as $id) {
                $dispatch = WorkshopDispatch::find($id);
                if (! $dispatch) {
                    continue;
                }

                if ($status === 'processing') {
                    $dispatch->update(['status' => 'processing']);
                    $dispatch->order->update(['status' => 'processing']);
                } elseif ($status === 'completed_at_workshop') {
                    $dispatch->update([
                        'status' => 'completed_at_workshop',
                        'completed_at_workshop_at' => now(),
                    ]);
                } elseif ($status === 'returned_to_branch') {
                    $dispatch->update([
                        'status' => 'returned_to_branch',
                        'returned_by' => auth()->id(),
                        'returned_at' => now(),
                    ]);
                    $dispatch->order->update(['status' => 'returned_from_workshop']);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    // ─── Branch Admin: Confirm order received back from workshop ───
    public function receivedAtBranch(WorkshopDispatch $dispatch, WhatsAppService $waService)
    {
        $dispatch->update([
            'status' => 'received_at_branch',
            'received_at_branch_at' => now(),
            'workshop_notes' => request('workshop_notes'),
        ]);

        $order = $dispatch->order;
        $order->update([
            'status' => 'done', // changed from returned_from_workshop to done
            'returned_from_workshop_at' => now(),
            'ready_at' => now(),
        ]);

        // Send WA "Ready" notification
        $waService->notifyOrderReady($order);

        return redirect()->back()->with('success', 'Order received back from workshop. Customer notified.');
    }

    // ─── View order tracking steps ───
    public function tracking(Order $order)
    {
        $dispatch = WorkshopDispatch::with(['dispatchedBy', 'receivedBy', 'returnedBy'])
            ->where('order_id', $order->id)
            ->latest()
            ->first();

        return view('workshop.tracking', compact('order', 'dispatch'));
    }

    // ─── Workshop Admin: All completed orders ───
    public function history()
    {
        $user = auth()->user();

        $dispatches = WorkshopDispatch::with(['order.customer', 'branch'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $user->branch_id))
            ->where('status', 'received_at_branch')
            ->orderByDesc('received_at_branch_at')
            ->paginate(20);

        return view('workshop.history', compact('dispatches'));
    }
}
