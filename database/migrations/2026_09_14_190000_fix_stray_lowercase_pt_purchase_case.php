<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix stray lowercase 'pt' to canonical 'Pt' for case #2704
        DB::table('pur.purcases')
            ->where('pcs_id', 2704)
            ->where('pcs_type', 'pt')
            ->update(['pcs_type' => 'Pt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pur.purcases')
            ->where('pcs_id', 2704)
            ->where('pcs_type', 'Pt')
            ->update(['pcs_type' => 'pt']);
    }
};
