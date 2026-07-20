<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE purnew.rfq DROP COLUMN IF EXISTS pcs_remarks');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE purnew.rfq ADD COLUMN pcs_remarks varchar');
    }
};
