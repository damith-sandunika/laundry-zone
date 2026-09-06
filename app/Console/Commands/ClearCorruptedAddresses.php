<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class ClearCorruptedAddresses extends Command
{
    protected $signature = 'clear:corrupted';

    public function handle()
    {
        DB::table('customers')->whereIn('id', [6, 11, 17, 18])->update(['address' => null]);
        $this->info("Cleared addresses");
        $this->info('Done.');
    }
}
