<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;

class EncryptAddresses extends Command
{
    protected $signature = 'customers:encrypt-addresses';
    protected $description = 'Encrypt all unencrypted customer addresses';

    public function handle()
    {
        $customers = \Illuminate\Support\Facades\DB::table('customers')->whereNotNull('address')->get();
        foreach ($customers as $c) {
            try {
                // Check if it's already a valid encrypted payload
                decrypt($c->address);
            } catch(\Exception $e) {
                // If it fails, it means it's plain text. Encrypt it manually.
                \Illuminate\Support\Facades\DB::table('customers')
                    ->where('id', $c->id)
                    ->update(['address' => encrypt($c->address)]);
                $this->info("Encrypted address for customer ID: {$c->id}");
            }
        }
        
        $this->info('Done.');
    }
}
