<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageService;
use App\Models\Customer;
use App\Models\Expense;
use Carbon\Carbon;

class PackageController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $customerPackages = CustomerPackage::where('branch_id', $branchId)
            ->with(['customer', 'package', 'services.serviceType'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $packages = Package::where('branch_id', $branchId)->where('is_active', true)->get();
        $customers = Customer::where('branch_id', $branchId)->get();

        return view('packages.index', compact('customerPackages', 'packages', 'customers'));
    }

    public function store(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::with('services')->findOrFail($request->package_id);
        
        // Expiry is 3 months according to user request "masa 3n exper wenn oni"
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(3);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $branchId, $package, $startDate, $endDate) {
            $customerPackage = CustomerPackage::create([
                'branch_id' => $branchId,
                'customer_id' => $request->customer_id,
                'package_id' => $package->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_status' => $package->payment_type === 'prepaid' ? 'paid' : 'pending'
            ]);

            // Check for rollover
            $previousServices = [];
            if ($package->is_rollover) {
                $lastPackage = CustomerPackage::where('customer_id', $request->customer_id)
                    ->where('package_id', $package->id)
                    ->where('status', '!=', 'exhausted')
                    ->latest()
                    ->first();

                if ($lastPackage) {
                    // Get remaining quantities
                    foreach ($lastPackage->services as $oldService) {
                        $remaining = max(0, $oldService->total_quantity - $oldService->used_quantity);
                        if ($remaining > 0) {
                            $previousServices[$oldService->service_type_id] = $remaining;
                        }
                    }
                    
                    // Mark old package as expired/renewed
                    $lastPackage->update(['status' => 'exhausted']);
                }
            }

            // Copy services and limits with rollover
            foreach ($package->services as $ps) {
                $rolloverQty = $previousServices[$ps->service_type_id] ?? 0;
                
                CustomerPackageService::create([
                    'customer_package_id' => $customerPackage->id,
                    'service_type_id' => $ps->service_type_id,
                    'total_quantity' => $ps->quantity_limit + $rolloverQty,
                    'used_quantity' => 0
                ]);
            }

            // If prepaid, record income in package_payments
            if ($package->payment_type === 'prepaid') {
                \App\Models\PackagePayment::create([
                    'customer_package_id' => $customerPackage->id,
                    'amount' => $package->price,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'created_by' => auth()->id()
                ]);
            }
        });

        return redirect()->route('packages.index')->with('success', 'Package assigned successfully!');
    }

    public function markPaid(CustomerPackage $customerPackage)
    {
        if ($customerPackage->payment_status === 'paid') {
            return redirect()->back()->with('error', 'Package is already paid.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($customerPackage) {
            $customerPackage->update(['payment_status' => 'paid']);

            Expense::create([
                'branch_id' => $customerPackage->branch_id,
                'type' => 'income',
                'category' => 'package_sale',
                'amount' => $customerPackage->package->price,
                'date' => Carbon::today(),
                'description' => "Package Sale ({$customerPackage->package->name}) - Paid Postpaid",
                'created_by' => auth()->id()
            ]);
        });

        return redirect()->back()->with('success', 'Package marked as paid.');
    }

    public function destroy(CustomerPackage $customerPackage)
    {
        $customerPackage->delete();
        return redirect()->back()->with('success', 'Customer package removed.');
    }

    public function printReceipt(CustomerPackage $customerPackage)
    {
        $customerPackage->load('customer', 'package', 'services.serviceType');
        $branch = \App\Models\Branch::find($customerPackage->branch_id);
        return view('packages.print', compact('customerPackage', 'branch'));
    }
}
