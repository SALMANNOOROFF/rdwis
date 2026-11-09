<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_action TYPE character varying(255)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_role TYPE character varying(150)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_from_stage TYPE character varying(100)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_to_stage TYPE character varying(100)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_user_rank TYPE character varying(100)");

        if (Schema::hasTable('fin.fund_shifts')) {
            DB::statement("ALTER TABLE fin.fund_shifts ALTER COLUMN fsh_current_holder TYPE character varying(255)");
            DB::statement("ALTER TABLE fin.fund_shifts ALTER COLUMN fsh_current_stage TYPE character varying(100)");
            DB::statement("ALTER TABLE fin.fund_shifts ALTER COLUMN fsh_status TYPE character varying(100)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_action TYPE character varying(50)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_role TYPE character varying(50)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_from_stage TYPE character varying(50)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_to_stage TYPE character varying(50)");
        DB::statement("ALTER TABLE fin.fund_shift_decisions ALTER COLUMN fsd_user_rank TYPE character varying(50)");
    }
};
