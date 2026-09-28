<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;

class FunctionalTestCommand extends Command
{
    protected $signature = 'test:functional';
    protected $description = 'Perform functional tests on CRUD operations (Add, Edit, Delete)';

    public function handle()
    {
        $this->info('Starting Functional Tests (Add, Edit, Delete)...');

        $admin = User::where('role', 'super_admin')->first() ?? User::first();
        auth()->login($admin);

        $passed = 0;
        $failed = 0;
        $failures = [];

        DB::beginTransaction();

        try {
            // 1. Test Customer Creation
            $this->info('Testing Customer Creation...');
            $customerController = app()->make(CustomerController::class);
            $request = Request::create('/customers', 'POST', [
                'name' => 'Test Customer',
                'phone' => '0712345678',
                'email' => 'test@example.com',
                'title' => 'Mr'
            ]);
            $response = $customerController->store($request);
            $customer = Customer::where('phone', '0712345678')->first();
            
            if ($customer) {
                $this->info('✅ Customer Created');
                $passed++;
            } else {
                $this->error('❌ Customer Creation Failed');
                $failed++;
                $failures[] = 'Customer Creation';
            }

            // 2. Test Customer Edit
            $this->info('Testing Customer Update...');
            if ($customer) {
                $updateRequest = Request::create('/customers/'.$customer->id, 'PUT', [
                    'name' => 'Test Customer Updated',
                    'phone' => '0712345678',
                ]);
                $customerController->update($updateRequest, $customer);
                $customer->refresh();
                if ($customer->name === 'Test Customer Updated') {
                    $this->info('✅ Customer Updated');
                    $passed++;
                } else {
                    $this->error('❌ Customer Update Failed');
                    $failed++;
                    $failures[] = 'Customer Update';
                }
            }

            // 3. Test Customer Delete
            $this->info('Testing Customer Deletion...');
            if ($customer) {
                $customerController->destroy($customer);
                if (Customer::find($customer->id) === null) {
                    $this->info('✅ Customer Deleted');
                    $passed++;
                } else {
                    $this->error('❌ Customer Deletion Failed');
                    $failed++;
                    $failures[] = 'Customer Deletion';
                }
            }


        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            $failed++;
            $failures[] = 'Exception occurred: ' . $e->getMessage();
        }

        DB::rollBack();

        $this->info("\nFunctional Test Complete!");
        $this->info("Passed: $passed, Failed: $failed");

        if (!empty($failures)) {
            $this->error("Failures detected:");
            foreach ($failures as $failure) {
                $this->error("- $failure");
            }
        }
    }
}
