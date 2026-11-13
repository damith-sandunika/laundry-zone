<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\PaymentSlip;
use App\Services\NotificationAlertService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationAlertService $alertService;

    public function __construct(NotificationAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function check()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['count' => 0, 'alerts' => []]);

        $alerts = [];
        $totalCount = 0;
        $isSuper = in_array($user->role, ['owner', 'super_admin']);

        // 1. Workshop Alerts
        if (in_array($user->role, ['workshop_admin', 'super_admin', 'owner'])) {
            $ws = $this->alertService->getWorkshopAlerts($user, $isSuper);
            $alerts = array_merge($alerts, $ws['alerts']);
            $totalCount += $ws['count'];
        }

        // 2. Branch Alerts
        if (in_array($user->role, ['branch_admin', 'super_admin', 'owner'])) {
            $br = $this->alertService->getBranchAlerts($user, $isSuper);
            $alerts = array_merge($alerts, $br['alerts']);
            $totalCount += $br['count'];
        }

        // 3. Payment Slips (Super Admin / Accountant)
        if ($isSuper || $user->role === 'accountant') {
            $pendingSlips = PaymentSlip::where('status', 'pending')->count();
            if ($pendingSlips > 0) {
                $alerts[] = [
                    'id' => 'pending_slips', 'label' => 'Payment Slips Awaiting Approval',
                    'count' => $pendingSlips, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'color' => 'amber', 'url' => route('orders.index', ['tab' => 'slips']),
                ];
                $totalCount += $pendingSlips;
            }
        }

        return response()->json([
            'count' => $totalCount,
            'alerts' => $alerts,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate(['alert_id' => 'required|string']);
        $user = auth()->user();

        NotificationLog::updateOrCreate(
            ['user_id' => $user->id, 'alert_id' => $request->alert_id],
            ['read_at' => now()]
        );

        return response()->json(['success' => true]);
    }
}
