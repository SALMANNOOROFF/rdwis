<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds approval_level column to hrforms.salary_bands and seeds mapping for all 15 designations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE hrforms.salary_bands 
            ADD COLUMN IF NOT EXISTS approval_level VARCHAR(30) NOT NULL DEFAULT 'MD_RDW' 
            CHECK (approval_level IN ('DG_NRDI', 'MD_RDW'));
        ");

        // Seed DG_NRDI (RO and above / Senior administrative positions)
        // ASSUMPTION pending HR confirmation for Manager & Assistant Manager
        DB::statement("
            UPDATE hrforms.salary_bands 
            SET approval_level = 'DG_NRDI' 
            WHERE designation IN ('PRO', 'SRO', 'RO', 'Director', 'Manager', 'Assistant Manager');
        ");

        // Seed MD_RDW (RT and below / Junior administrative / Student / Support positions)
        // ASSUMPTION pending HR confirmation for Junior Assistant
        DB::statement("
            UPDATE hrforms.salary_bands 
            SET approval_level = 'MD_RDW' 
            WHERE designation IN ('SRT', 'RT', 'JRT', 'LA', 'RA', 'EA', 'Intern', 'Junior Assistant', 'Support Staff');
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE hrforms.salary_bands DROP COLUMN IF EXISTS approval_level;");
    }
};
