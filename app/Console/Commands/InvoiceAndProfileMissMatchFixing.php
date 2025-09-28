<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InvoiceAndProfileMissMatchFixing extends Command
{
    protected $signature = 'fix:invoiceAndProfileMissmatch';
    protected $description = 'Fix mismatches between invoices total dues and profiles due';

    public function handle()
    {
        DB::statement("
           UPDATE clients 
                SET due = (
                    SELECT COALESCE(SUM(invoices.due), 0) 
                    FROM invoices 
                    WHERE invoices.client_id = clients.id 
                    AND invoices.deleted_at IS NULL
                    AND invoices.due > 0 
                )
                WHERE clients.deleted_at IS NULL;
        ");
        return Command::SUCCESS;
    }
}
