<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchLog;
use App\Models\BranchReopenRequest;
use Illuminate\Http\Request;

class BranchLogController extends Controller
{
    public function index(Request $request)
    {
        // Only super_admin should access this (enforced via middleware in routes)
        $tab = $request->get('tab', 'logs'); // 'logs' or 'reopen_requests'

        $branches = Branch::all();

        // ── Shift Logs Query ──
        $logsQuery = BranchLog::with(['branch', 'user'])->latest();

        if ($request->filled('branch_id')) {
            $logsQuery->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date')) {
            $logsQuery->whereDate('created_at', $request->date);
        }

        $logs = $logsQuery->paginate(15, ['*'], 'logs_page')->withQueryString();

        // ── Re-open Requests Query ──
        $reopenQuery = BranchReopenRequest::with(['branch', 'requestedBy', 'reviewedBy'])->latest();

        if ($request->filled('request_status')) {
            $reopenQuery->where('status', $request->request_status);
        }

        if ($request->filled('branch_id')) {
            $reopenQuery->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date')) {
            $reopenQuery->where('request_date', $request->date);
        }

        $reopenRequests = $reopenQuery->paginate(15, ['*'], 'requests_page')->withQueryString();

        $pendingReopenCount = BranchReopenRequest::where('status', 'pending')->count();

        return view('superadmin.branch-logs', compact('logs', 'branches', 'reopenRequests', 'pendingReopenCount', 'tab'));
    }
}


