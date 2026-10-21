<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE hr.ctrcaseremarks ALTER COLUMN crr_status TYPE character varying(255)");
        DB::statement("ALTER TABLE hr.ctrcaseremarks ALTER COLUMN crr_username TYPE character varying(255)");
        DB::statement("ALTER TABLE hr.ctrcaseremarks ALTER COLUMN crr_user_rank TYPE character varying(255)");
        DB::statement("ALTER TABLE hr.ctrcaseremarks ALTER COLUMN crr_user_desig TYPE character varying(255)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE hr.ctrcaseremarks ALTER COLUMN crr_status TYPE character varying(50)");
    }
};
