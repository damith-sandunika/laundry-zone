<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\WorkshopDispatch;
use Illuminate\Http\Request;

class WorkshopOperationsController extends Controller
{
    public function operations(Request $request)
    {
        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        $query = WorkshopDispatch::with(['order.customer', 'order.deliveryAppointment', 'order.paymentTransactions', 'order.cubby.cubby', 'order.items.serviceType', 'branch', 'dispatchedBy'])
            ->join('orders', 'workshop_dispatches.order_id', '=', 'orders.id')
            ->whereIn('workshop_dispatches.id', function ($q) {
                $q->selectRaw('MAX(id)')
                  ->from('workshop_dispatches')
                  ->groupBy('order_id');
            })
            ->select('workshop_dispatches.*')
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->whereNotIn('workshop_dispatches.status', ['received_at_branch'])
            ->orderByDesc('orders.id');

        $selectedBranch = $request->get('branch');
        if ($selectedBranch) {
            $query->where('workshop_dispatches.branch_id', $selectedBranch);
        }

        $countsQuery = clone $query;
        $counts = [
            'incoming' => (clone $countsQuery)->where('workshop_dispatches.status', 'dispatched_to_workshop')->count(),
            'to_wash' => (clone $countsQuery)->where('workshop_dispatches.status', 'received_at_workshop')->count(),
            'washing' => (clone $countsQuery)->where('workshop_dispatches.status', 'processing')->count(),
            'completed' => (clone $countsQuery)->where('workshop_dispatches.status', 'completed_at_workshop')->count(),
            'returned' => (clone $countsQuery)->whereIn('workshop_dispatches.status', ['returned_to_branch', 'received_at_branch'])->count(),
        ];

        $currentTab = $request->get('tab', 'incoming');
        if ($currentTab === 'incoming') {
            $query->where('workshop_dispatches.status', 'dispatched_to_workshop');
        } elseif ($currentTab === 'to_wash') {
            $query->where('workshop_dispatches.status', 'received_at_workshop');
        } elseif ($currentTab === 'washing') {
            $query->where('workshop_dispatches.status', 'processing');
        } elseif ($currentTab === 'completed') {
            $query->where('workshop_dispatches.status', 'completed_at_workshop');
        } elseif ($currentTab === 'returned') {
            $query->whereIn('workshop_dispatches.status', ['returned_to_branch', 'received_at_branch']);
        }

        $dispatches = $query->paginate(15);

        $completedToday = WorkshopDispatch::whereIn('status', ['received_at_branch', 'returned_to_branch'])
            ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('workshop_branch_id', $branchId))
            ->where(function($q) {
                $q->whereDate('received_at_branch_at', today())
                  ->orWhereDate('returned_at', today());
            })
            ->count();

        $branches = Branch::where('type', 'branch')->get();
        $riders = \App\Models\User::where('role', 'rider')->get();
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->where('is_active', true)->get();
        $categories = \App\Models\ServiceCategory::with('services')->where('is_active', true)->get();
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('workshop.operations', compact('dispatches', 'completedToday', 'branches', 'riders', 'cubbies', 'categories', 'minimumDeliveryCost', 'currentTab', 'selectedBranch', 'counts'));
    }

    public function receive(WorkshopDispatch $dispatch, \App\Actions\Workshop\ReceiveWorkshopDispatchAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'status' => 'received_at_workshop']);
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::receive error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function startProcessing(WorkshopDispatch $dispatch, \App\Actions\Workshop\StartProcessingWorkshopAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'status' => 'processing', 'order_number' => $dispatch->order->order_number, 'order_id' => $dispatch->order->id]);
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::startProcessing error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function complete(\App\Http\Requests\Workshop\CompleteWorkshopProcessingRequest $request, WorkshopDispatch $dispatch, \App\Actions\Workshop\CompleteWorkshopProcessingAction $action)
    {
        try {
            $action->execute($dispatch, $request->validated(), auth()->id());
            return response()->json(['success' => true, 'status' => 'completed_at_workshop', 'order_number' => $dispatch->order->order_number, 'order_id' => $dispatch->order->id]);
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::complete error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function returnToBranch(WorkshopDispatch $dispatch, \App\Actions\Workshop\ReturnToBranchAction $action)
    {
        try {
            $action->execute($dispatch, auth()->id());
            return response()->json(['success' => true, 'status' => 'returned_to_branch']);
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::returnToBranch error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function dispatchDelivery(\App\Http\Requests\Workshop\WorkshopDispatchDeliveryRequest $request, WorkshopDispatch $dispatch, \App\Actions\Workshop\WorkshopDispatchDeliveryAction $action)
    {
        try {
            $result = $action->execute($dispatch, $request->validated(), auth()->id());
            return response()->json(array_merge(['success' => true], $result));
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::dispatchDelivery error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function bulkStatus(\App\Http\Requests\Workshop\WorkshopBulkStatusUpdateRequest $request, \App\Actions\Workshop\WorkshopBulkStatusUpdateAction $action)
    {
        try {
            $action->execute($request->validated(), auth()->id());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::bulkStatus error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function handover(\App\Http\Requests\Workshop\WorkshopHandoverRequest $request, WorkshopDispatch $dispatch, \App\Actions\Workshop\WorkshopHandoverAction $action)
    {
        try {
            $action->execute($dispatch, $request->validated(), auth()->id());
            return response()->json(['success' => true, 'order_id' => $dispatch->order_id, 'order_number' => $dispatch->order->order_number]);
        } catch (\Exception $e) {
            \Log::error('WorkshopOperationsController::handover error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }
}
