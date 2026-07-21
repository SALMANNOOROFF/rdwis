<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix purcases rows where pcs_midprice is 0 but pcs_price > 0.
     *
     * In legacy RDWIS and fin.docs_ipc, amount1 (for transtype = 1) is mapped to
     * pcs_midprice. When cases were saved in the new app without setting pcs_midprice,
     * pcs_midprice remained 0, causing fin.docs_ipc to report 0 for "In Process" amount.
     */
    public function up(): void
    {
        DB::table('pur.purcases')
            ->where('pcs_price', '>', 0)
            ->where('pcs_midprice', 0)
            ->update([
                'pcs_midprice' => DB::raw('pcs_price')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversible down operation needed for a data fix
    }
};
