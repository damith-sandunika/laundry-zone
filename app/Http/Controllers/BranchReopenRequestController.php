<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchLog;
use App\Models\BranchReopenRequest;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BranchReopenRequestController extends Controller
{
    /**
     * Submit a request from Branch/Workshop Admin to Super Admin for shift re-open.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = auth()->user();
        $branchId = $this->getActiveBranchId();

        // Check if there is already a pending request for today
        $existing = BranchReopenRequest::where('branch_id', $branchId)
            ->where('request_date', now()->toDateString())
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'A re-open request is already pending for today. Please wait for Super Admin approval.');
        }

        $reopenRequest = BranchReopenRequest::create([
            'branch_id'    => $branchId,
            'requested_by' => $user->id,
            'reason'       => $request->reason,
            'status'       => 'pending',
            'request_date' => now()->toDateString(),
        ]);

        $branchName = $user->branch->name ?? 'Branch';

        // Notify Super Admins
        $superAdmins = User::where('role', 'super_admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send(
                $superAdmins,
                new SystemAlertNotification(
                    "Branch Re-Open Request: {$branchName} has requested Super Admin approval to re-open shift today. Reason: {$request->reason}",
                    route('superadmin.branch-logs', ['tab' => 'reopen_requests']),
                    'warning'
                )
            );
        }

        // Branch Log
        BranchLog::create([
            'branch_id'   => $branchId,
            'user_id'     => $user->id,
            'action'      => 'reopen_requested',
            'description' => "Requested shift re-open approval from Super Admin. Reason: {$request->reason}",
        ]);

        return redirect()->back()->with('success', 'Your re-open request has been sent to Super Admin. You will be able to open the shift once approved.');
    }

    /**
     * Super Admin views all reopen requests.
     */
    public function index(Request $request)
    {
        $query = BranchReopenRequest::with(['branch', 'requestedBy', 'reviewedBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date')) {
            $query->where('request_date', $request->date);
        }

        $requests = $query->paginate(15)->withQueryString();
        $branches = Branch::all();

        return view('superadmin.reopen-requests', compact('requests', 'branches'));
    }

    /**
     * Super Admin approves a reopen request.
     */
    public function approve(Request $request, BranchReopenRequest $reopenRequest)
    {
        if ($reopenRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been reviewed.');
        }

        DB::transaction(function () use ($reopenRequest, $request) {
            $reopenRequest->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $request->input('admin_notes', 'Approved by Super Admin'),
            ]);

            BranchLog::create([
                'branch_id'   => $reopenRequest->branch_id,
                'user_id'     => auth()->id(),
                'action'      => 'reopen_approved',
                'description' => "Shift re-open request approved by Super Admin.",
            ]);

            // Notify the branch admin who requested it
            if ($reopenRequest->requestedBy) {
                $reopenRequest->requestedBy->notify(
                    new SystemAlertNotification(
                        "Your shift re-open request for today has been APPROVED by Super Admin! You can now open your shift.",
                        route('dashboard'),
                        'success'
                    )
                );
            }
        });

        return redirect()->back()->with('success', "Re-open request for {$reopenRequest->branch->name} approved successfully.");
    }

    /**
     * Super Admin rejects a reopen request.
     */
    public function reject(Request $request, BranchReopenRequest $reopenRequest)
    {
        if ($reopenRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'This request has already been reviewed.');
        }

        DB::transaction(function () use ($reopenRequest, $request) {
            $reopenRequest->update([
                'status'      => 'rejected',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $request->input('admin_notes', 'Rejected by Super Admin'),
            ]);

            BranchLog::create([
                'branch_id'   => $reopenRequest->branch_id,
                'user_id'     => auth()->id(),
                'action'      => 'reopen_rejected',
                'description' => "Shift re-open request rejected by Super Admin. Notes: " . ($request->input('admin_notes') ?? 'None'),
            ]);

            // Notify requester
            if ($reopenRequest->requestedBy) {
                $reopenRequest->requestedBy->notify(
                    new SystemAlertNotification(
                        "Your shift re-open request for today was REJECTED by Super Admin. Note: " . ($request->input('admin_notes') ?? 'Request declined'),
                        route('dashboard'),
                        'danger'
                    )
                );
            }
        });

        return redirect()->back()->with('success', "Re-open request for {$reopenRequest->branch->name} has been rejected.");
    }
}

