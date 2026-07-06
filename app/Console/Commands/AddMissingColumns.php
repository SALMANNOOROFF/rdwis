<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddMissingColumns extends Command
{
    protected $signature = 'db:add-missing-columns';
    protected $description = 'Add missing columns to hr.ctrcases';

    public function handle()
    {
        $this->info('Adding missing columns...');
        
        try {
            DB::statement('ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_divisionid integer');
            DB::statement('ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_createdby integer');
            DB::statement('ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_createdat timestamp without time zone');
            DB::statement('ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_releasedby integer');
            
            $this->info('Columns added successfully!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
