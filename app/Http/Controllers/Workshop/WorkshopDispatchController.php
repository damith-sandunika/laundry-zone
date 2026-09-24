<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WorkshopDispatch;
use Illuminate\Http\Request;

class WorkshopDispatchController extends Controller
{
    public function dispatch(\App\Http\Requests\Workshop\DispatchToWorkshopRequest $request, Order $order, \App\Actions\Workshop\DispatchToWorkshopAction $action)
    {
        try {
            $action->execute($order, $request->validated(), auth()->id());
            return redirect()->back()->with('success', 'Order dispatched to workshop successfully.');
        } catch (\Exception $e) {
            \Log::error('WorkshopDispatchController::dispatch error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred loading the workshop.');
        }
    }

    public function bulkDispatch(\App\Http\Requests\Workshop\BulkDispatchToWorkshopRequest $request, \App\Actions\Workshop\BulkDispatchToWorkshopAction $action)
    {
        try {
            $count = $action->execute($request->validated(), auth()->id());
            return redirect()->back()->with('success', $count . ' orders dispatched to workshop.');
        } catch (\Exception $e) {
            \Log::error('WorkshopDispatchController::bulkDispatch error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred during bulk dispatch.');
        }
    }

    public function receivedAtBranch(WorkshopDispatch $dispatch, Request $request, \App\Actions\Workshop\MarkReceivedAtBranchAction $action)
    {
        try {
            $action->execute($dispatch, $request->all());
            return redirect()->back()->with('success', 'Order received back from workshop. Customer notified.');
        } catch (\Exception $e) {
            \Log::error('WorkshopDispatchController::receivedAtBranch error', [
                'dispatch_id' => $dispatch->id ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'An error occurred during dispatch.');
        }
    }
}
