<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryAppointment;
use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    // ─── Branch Admin: View All Delivery Appointments ───
    public function index(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        // Super admins ONLY have full access to all branches
        $hasFullAccess = $user->isSuperAdmin();

        // Branch admins and staff can only access if delivery is enabled for their branch
        if (! $hasFullAccess && (! $user->branch || ! $user->branch->is_delivery_enabled)) {
            // Workshop admins can still access delivery page (for receiving back orders)
            $isWorkshopAdmin = $user->role === 'workshop_admin' || ($user->role === 'staff' && $user->branch && $user->branch->type === 'workshop');
            if (! $isWorkshopAdmin) {
                abort(403, 'Delivery operations are not enabled for your branch.');
            }
        }

        $query = DeliveryAppointment::with(['customer', 'branch', 'deliveryTasks.rider', 'order.paymentTransactions', 'order.items.serviceType.category', 'order.items.extras', 'cancelledBy'])
            ->orderByDesc('id');

        // Always scope to own branch — only super_admin sees all
        if (! $hasFullAccess) {
            $query->where('branch_id', $branchId);
        }

        // Get the active tab from the request
        $currentTab = $request->get('tab', 'pickups');

        // We need total counts for the UI stat cards (without pagination)
        $pickupsCountQuery = clone $query;
        $pickupsCount = $pickupsCountQuery->whereIn('status', ['pending', 'confirmed', 'rider_assigned', 'picked_up', 'order_created', 'returned_from_delivery'])->count();

        $returnsCountQuery = clone $query;
        // For returns, we need to filter out delivered orders that don't have pending cash.
        // Doing this accurately in SQL is complex because of the payment logic, so we will
        // fetch the IDs that match the condition or just do a raw count for non-delivered,
        // and then manually add the delivered ones that require cash collection.
        // For simplicity of counts, we can do a slightly approximated count or exact.
        $allReturns = $returnsCountQuery->whereIn('status', ['ready', 'out_for_delivery', 'delivered'])
            ->where('delivery_preference', 'deliver_to_address')
            ->get();
        
        $validReturnsIds = $allReturns->filter(function ($appt) {
            if ($appt->status === 'delivered') {
                $order = $appt->order;
                if (! $order) return false;
                if ($order->payment_status !== 'paid') return true;
                return PaymentTransaction::where('order_id', $order->id)
                    ->where('payment_method', 'cash')
                    ->whereNull('collected_from_rider_at')
                    ->whereNotNull('created_by')
                    ->exists();
            }
            return true;
        })->pluck('id');

        $returnsCount = $validReturnsIds->count();

        // Now filter the main query based on the active tab for pagination
        if ($currentTab === 'returns') {
            $query->whereIn('id', $validReturnsIds);
        } else {
            $query->whereIn('status', ['pending', 'confirmed', 'rider_assigned', 'picked_up', 'order_created', 'returned_from_delivery']);
        }

        $appointments = $query->paginate(15);

        $branches = $hasFullAccess ? Branch::where('is_active', true)->get() : collect();

        $ridersQuery = User::where('role', 'rider');
        if (! $hasFullAccess) {
            $ridersQuery->where('branch_id', $branchId);
        }
        $riders = $ridersQuery->get();

        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->where('is_active', true)->get();

        return view('delivery.index', compact('appointments', 'currentTab', 'pickupsCount', 'returnsCount', 'branches', 'riders', 'cubbies'));
    }

    // ─── Public routes moved to CustomerSiteController ───

    // ─── Branch Admin: Confirm Appointment & Assign Rider ───
    public function confirm(DeliveryAppointment $appointment)
    {
        $branchId = $appointment->branch_id;
        $riders = User::where('role', 'rider')->where('branch_id', $branchId)->where('is_active', true)->get();

        return view('delivery.confirm', compact('appointment', 'riders'));
    }

    public function confirmAction(DeliveryAppointment $appointment, \App\Actions\Delivery\ConfirmAppointmentAction $action)
    {
        try {
            $action->execute($appointment);
            return redirect()->route('delivery.index')->with('success', 'Appointment confirmed. Customer notified.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@confirmAction: ' . $e->getMessage());
            return redirect()->route('delivery.index')->with('error', 'Could not confirm appointment. Please try again.');
        }
    }

    public function cancel(\App\Http\Requests\Delivery\CancelAppointmentRequest $request, DeliveryAppointment $appointment, \App\Actions\Delivery\CancelAppointmentAction $action)
    {
        try {
            $action->execute($appointment, $request->validated());
            return redirect()->route('delivery.index')->with('success', 'Appointment cancelled. Customer notified.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@cancel: ' . $e->getMessage());
            return redirect()->route('delivery.index')->with('error', 'Could not cancel appointment. Please try again.');
        }
    }

    public function assignRider(\App\Http\Requests\Delivery\AssignPickupRiderRequest $request, DeliveryAppointment $appointment, \App\Actions\Delivery\AssignPickupRiderAction $action)
    {
        try {
            $rider = $action->execute($appointment, $request->validated());

            if ($request->delivery_method === 'pickme_flash') {
                return redirect()->route('delivery.index')->with('success', "PickMe assigned. Rider is on the way to customer.");
            }

            return redirect()->route('delivery.index')->with('success', "Rider {$rider->name} assigned for pickup.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@assignRider: ' . $e->getMessage());
            return redirect()->route('delivery.index')->with('error', 'Could not assign rider. Please try again.');
        }
    }

    // ─── Branch Admin: Mark order received at branch, assign delivery rider ───
    public function markAtBranch(\App\Http\Requests\Delivery\MarkAtBranchRequest $request, DeliveryAppointment $appointment, \App\Actions\Delivery\MarkAtBranchAction $action)
    {
        try {
            $dispatch = $action->execute($appointment, $request->validated());

            $isWorkshopAdmin = \Illuminate\Support\Facades\Gate::allows('is-workshop-admin');
            $targetRouteName = $isWorkshopAdmin ? 'workshop.operations' : 'orders.index';

            return redirect()->route($targetRouteName)->with('success', 'Delivery arrived! Order has been moved to the Operations page.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@markAtBranch: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not mark order as arrived. Please try again.');
        }
    }

    public function assignDeliveryRider(\App\Http\Requests\Delivery\AssignDropoffRiderRequest $request, DeliveryAppointment $appointment, \App\Actions\Delivery\AssignDropoffRiderAction $action)
    {
        try {
            $rider = $action->execute($appointment, $request->validated());

            if ($request->rider_id === 'pickme_flash') {
                return redirect()->back()->with('success', "Assigned to PickMe/Flash. Order automatically marked as Delivered & Completed.")->with('print_url', route('orders.printChit', $appointment->order->id));
            }

            return redirect()->back()->with('success', "Rider {$rider->name} assigned for delivery.")->with('print_url', route('orders.printChit', $appointment->order->id));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@assignDeliveryRider: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not assign delivery rider. Please try again.');
        }
    }

    public function collectCash(\App\Http\Requests\Delivery\CollectCashRequest $request, DeliveryAppointment $appointment, \App\Actions\Delivery\CollectCashAction $action)
    {
        try {
            $success = $action->execute($appointment, $request->validated());

            if (!$success) {
                return redirect()->back()->with('error', 'No order found for this appointment.');
            }

            return redirect()->back()->with('success', 'Verified and collected cash from rider. Order marked as completed.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@collectCash: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not collect cash. Please try again.');
        }
    }

    // ─── Handle Confirm Return (Delivery Failure) ───
    public function confirmReturn(Request $request, $orderId)
    {
        $request->validate([
            'cubby_id' => 'required|exists:cubbies,id'
        ]);

        try {
            $order = \App\Models\Order::findOrFail($orderId);
            
            // Assign to cubby
            \App\Models\OrderCubby::updateOrCreate(
                ['order_id' => $order->id],
                ['cubby_id' => $request->cubby_id, 'assigned_at' => now()]
            );
            \App\Models\Cubby::where('id', $request->cubby_id)->update(['status' => 'occupied']);

            $isWorkshop = auth()->user()->branch && auth()->user()->branch->type === 'workshop';
            $status = $isWorkshop ? 'processing' : 'done';

            $order->update(['status' => $status]);

            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $status,
                'changed_by' => auth()->id(),
                'note' => 'Return confirmed. Assigned to cubby and marked as ' . $status . '.',
            ]);

            // Update workshop dispatch if workshop
            if ($isWorkshop) {
                \App\Models\WorkshopDispatch::where('order_id', $order->id)
                    ->orderBy('id', 'desc')
                    ->first()?->update(['status' => 'completed_at_workshop']);
            }

            // Update appointment
            if ($order->deliveryAppointment) {
                $order->deliveryAppointment->update(['status' => 'returned']);
            }

            return redirect()->route($isWorkshop ? 'workshop.operations' : 'orders.index', ['tab' => 'completed'])->with('success', 'Return confirmed and assigned to cubby.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DeliveryController@confirmReturn: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Could not confirm return. Please try again.');
        }
    }
}

