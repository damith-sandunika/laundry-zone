<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageService;
use App\Models\Expense;
use App\Models\Package;
use App\Models\PackagePayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PackageController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $customerPackages = CustomerPackage::with(['customer', 'package', 'services.serviceType'])
            ->orderBy('created_at', 'desc')
            ->get();

        $packages = Package::where('is_active', true)->get();
        $customers = Customer::all();

        return view('packages.index', compact('customerPackages', 'packages', 'customers'));
    }

    public function store(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id',
        ]);

        $package = Package::with('services')->findOrFail($request->package_id);

        // Expiry is 3 months according to user request "masa 3n exper wenn oni"
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(3);

        DB::transaction(function () use ($request, $branchId, $package, $startDate, $endDate) {
            $customerPackage = CustomerPackage::create([
                'branch_id' => $branchId,
                'customer_id' => $request->customer_id,
                'package_id' => $package->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_status' => $package->payment_type === 'prepaid' ? 'paid' : 'pending',
            ]);

            // Copy services and limits
            foreach ($package->services as $ps) {
                CustomerPackageService::create([
                    'customer_package_id' => $customerPackage->id,
                    'service_type_id' => $ps->service_type_id,
                    'total_quantity' => $ps->quantity_limit,
                    'used_quantity' => 0,
                ]);
            }

            // If prepaid, record income in package_payments
            if ($package->payment_type === 'prepaid') {
                PackagePayment::create([
                    'customer_package_id' => $customerPackage->id,
                    'amount' => $package->price,
                    'payment_method' => $request->payment_method ?? 'cash',
                    'created_by' => auth()->id(),
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

        DB::transaction(function () use ($customerPackage) {
            $customerPackage->update(['payment_status' => 'paid']);

            Expense::create([
                'branch_id' => $customerPackage->branch_id,
                'type' => 'income',
                'category' => 'package_sale',
                'amount' => $customerPackage->package->price,
                'date' => Carbon::today(),
                'description' => "Package Sale ({$customerPackage->package->name}) - Paid Postpaid",
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Package marked as paid.');
    }

    public function destroy(Request $request, CustomerPackage $customerPackage)
    {
        $request->validate(['admin_password' => 'required']);

        $admins = User::whereIn('role', ['branch_admin', 'super_admin', 'staff'])
            ->where('branch_id', $customerPackage->branch_id)
            ->get();

        $authorized = false;
        foreach ($admins as $admin) {
            if (Hash::check($request->admin_password, $admin->password)) {
                $authorized = true;
                break;
            }
        }

        if (! $authorized) {
            return redirect()->back()->with('error', 'Invalid admin password. Deletion unauthorized.');
        }

        DB::transaction(function () use ($customerPackage) {
            // Revert prepaid income
            PackagePayment::where('customer_package_id', $customerPackage->id)->delete();

            // Revert postpaid income
            $expense = Expense::where('branch_id', $customerPackage->branch_id)
                ->where('description', "Package Sale ({$customerPackage->package->name}) - Paid Postpaid")
                ->latest()
                ->first();

            if ($expense) {
                $expense->delete();
            }

            $customerPackage->delete();
        });

        return redirect()->back()->with('success', 'Customer package removed and accounting cleared.');
    }

    public function printReceipt(CustomerPackage $customerPackage)
    {
        $customerPackage->load('customer', 'package', 'services.serviceType');
        $branch = Branch::find($customerPackage->branch_id);

        return view('packages.print', compact('customerPackage', 'branch'));
    }
}
