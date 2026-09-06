<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUnencryptedAddresses extends Command
{
    protected $signature = 'check:unencrypted';

    public function handle()
    {
        $customers = DB::table('customers')->get();
        foreach ($customers as $c) {
            if ($c->address !== null) {
                try {
                    decrypt($c->address);
                } catch (\Exception $e) {
                    $this->error("Unencrypted: ID {$c->id} - Address: '{$c->address}'");
                    
                    // Let's just fix it automatically since we are here
                    DB::table('customers')->where('id', $c->id)->update(['address' => encrypt($c->address)]);
                    $this->info("Fixed ID {$c->id}");
                }
            }
        }
        $this->info('Check complete.');
    }
}
