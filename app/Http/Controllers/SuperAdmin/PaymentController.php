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

        // SMS Credits History
        $smsCredits = \App\Models\Central\TenantSmsCredit::with('tenant')->latest()->paginate(10, ['*'], 'sms_page');
        $totalSmsRevenue = \App\Models\Central\TenantSmsCredit::where('status', 'completed')->sum('amount_paid');
        $totalSmsIssued = \App\Models\Central\TenantSmsCredit::where('status', 'completed')->sum('sms_count');

        return view('super_admin.payments.index', compact(
            'subscriptions',
            'totalCollected',
            'pendingAmount',
            'overdueAmount',
            'tenants',
            'smsCredits',
            'totalSmsRevenue',
            'totalSmsIssued'
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
            'amount' => 'nullable|numeric|min:0',
            'billing_month' => 'required|date_format:Y-m',
            'status' => 'nullable|in:paid,pending,overdue',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);

        $totalAmount = $request->filled('amount') 
            ? (float) $request->amount 
            : $tenant->total_monthly_price;

        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $request->billing_month)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();
        $status = $request->status ?? 'paid';

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(4)) . '-' . date('Ymd'),
            'plan_name' => $tenant->plan ?? 'Standard Plan',
            'base_amount' => (float) ($tenant->plan_price ?? $totalAmount),
            'delivery_addon_amount' => $tenant->is_delivery_enabled ? (float) ($tenant->delivery_price ?? 0) : 0,
            'total_amount' => $totalAmount,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'billing_period_start' => $startDate,
            'billing_period_end' => $endDate,
            'status' => $status,
            'payment_method' => $request->payment_method ?? 'Bank Transfer',
            'payment_reference' => $request->payment_reference,
            'paid_at' => $status === 'paid' ? now() : null,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Subscription invoice generated successfully for ' . $startDate->format('F Y') . '.');
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
     * Add SMS Credits to a Tenant (SaaS Admin).
     */
    public function storeSmsCredit(Request $request)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'tenant_id' => 'required|exists:landlord.tenants,id',
            'sms_count' => 'required|integer|min:1',
            'rate_per_sms' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        $rate = $request->filled('rate_per_sms') ? (float)$request->rate_per_sms : (float)$tenant->sms_rate;
        $count = (int)$request->sms_count;
        $amountPaid = $request->filled('amount_paid') ? (float)$request->amount_paid : ($count * $rate);

        // Record credit package
        \App\Models\Central\TenantSmsCredit::create([
            'tenant_id' => $tenant->id,
            'sms_count' => $count,
            'rate_per_sms' => $rate,
            'amount_paid' => $amountPaid,
            'payment_method' => $request->payment_method ?? 'Bank Transfer',
            'payment_reference' => $request->payment_reference,
            'status' => 'completed',
            'notes' => $request->notes ?? 'SMS Bundle Top-Up by SaaS Admin',
        ]);

        // Increment tenant sms balance and update rate if changed
        $tenant->increment('sms_balance', $count);
        if ($request->filled('rate_per_sms')) {
            $tenant->sms_rate = $rate;
            $tenant->save();
        }

        return back()->with('success', "Successfully added {$count} SMS credits to {$tenant->name}. New Balance: {$tenant->sms_balance} SMS.");
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
