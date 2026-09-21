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
        // 1. Update role template in cen.roles for Head Quarters
        DB::table('cen.roles')
            ->where('rol_unt_id', 10000)
            ->where('rol_desig', 'Director NRD')
            ->update([
                'rol_desig'      => 'Deputy Director General NRDI',
                'rol_desigshort' => 'DDG NRDI',
                'rol_auth'       => 'approver',
            ]);

        // 2. Update any existing accounts in cen.accounts if present
        DB::table('cen.accounts')
            ->where('acc_desig', 'Director NRD')
            ->update([
                'acc_desig'      => 'Deputy Director General NRDI',
                'acc_desigshort' => 'DDG NRDI',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('cen.accounts')
            ->where('acc_desig', 'Deputy Director General NRDI')
            ->update([
                'acc_desig'      => 'Director NRD',
                'acc_desigshort' => 'DNRD',
            ]);

        DB::table('cen.roles')
            ->where('rol_unt_id', 10000)
            ->where('rol_desig', 'Deputy Director General NRDI')
            ->update([
                'rol_desig'      => 'Director NRD',
                'rol_desigshort' => 'DNRD',
                'rol_auth'       => 'viewer',
            ]);
    }
};
