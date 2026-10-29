<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\TenantSubscription;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * List subscriptions and payment records.
     */
    public function index(Request $request)
    {
        TenantManager::resetToLandlord();

        $query = TenantSubscription::with('tenant');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhere('payment_reference', 'like', "%{$s}%")
                    ->orWhereHas('tenant', function ($tq) use ($s) {
                        $tq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        $subscriptions = $query->latest()->paginate(15)->withQueryString();

        $totalCollected = TenantSubscription::where('status', 'paid')->sum('total_amount');
        $pendingAmount = TenantSubscription::where('status', 'pending')->sum('total_amount');
        $overdueAmount = TenantSubscription::where('status', 'overdue')->sum('total_amount');
        $tenants = Tenant::where('status', 'active')->get();

        return view('super_admin.payments.index', compact(
            'subscriptions',
            'totalCollected',
            'pendingAmount',
            'overdueAmount',
            'tenants'
        ));
    }

    /**
     * Create / Record a new invoice or subscription payment.
     */
    public function store(Request $request)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'tenant_id' => 'required|exists:landlord.tenants,id',
            'plan_name' => 'required|string|max:100',
            'base_amount' => 'required|numeric|min:0',
            'delivery_addon_amount' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'billing_period_start' => 'nullable|date',
            'billing_period_end' => 'nullable|date',
            'status' => 'required|in:paid,pending,overdue',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $base = (float) $request->base_amount;
        $delivery = (float) ($request->delivery_addon_amount ?? 0);
        $total = $base + $delivery;

        TenantSubscription::create([
            'tenant_id' => $request->tenant_id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(4)) . '-' . date('Ymd'),
            'plan_name' => $request->plan_name,
            'base_amount' => $base,
            'delivery_addon_amount' => $delivery,
            'total_amount' => $total,
            'currency' => 'LKR',
            'billing_cycle' => $request->billing_cycle,
            'billing_period_start' => $request->billing_period_start ?? now()->startOfMonth(),
            'billing_period_end' => $request->billing_period_end ?? now()->endOfMonth(),
            'status' => $request->status,
            'payment_method' => $request->payment_method ?? 'Bank Transfer',
            'payment_reference' => $request->payment_reference,
            'paid_at' => $request->status === 'paid' ? now() : null,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Subscription invoice generated successfully.');
    }

    /**
     * Update invoice payment status.
     */
    public function updateStatus(Request $request, TenantSubscription $subscription)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'status' => 'required|in:paid,pending,overdue,cancelled',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $subscription->status = $request->status;
        if ($request->status === 'paid' && !$subscription->paid_at) {
            $subscription->paid_at = now();
        }
        if ($request->filled('payment_method')) {
            $subscription->payment_method = $request->payment_method;
        }
        if ($request->filled('payment_reference')) {
            $subscription->payment_reference = $request->payment_reference;
        }
        $subscription->save();

        return back()->with('success', "Invoice {$subscription->invoice_number} status updated to {$subscription->status}.");
    }

    /**
     * Show / Print subscription invoice.
     */
    public function invoice(TenantSubscription $subscription)
    {
        TenantManager::resetToLandlord();

        $subscription->load('tenant');
        return view('super_admin.payments.invoice', compact('subscription'));
    }
}
