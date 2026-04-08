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
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS pur.purdecisions (
                pdec_id          SERIAL PRIMARY KEY,
                pdec_pcs_id      INTEGER NOT NULL,
                pdec_acc_id      INTEGER NOT NULL,
                pdec_role        VARCHAR(50) NOT NULL,
                pdec_action      VARCHAR(50) NOT NULL,
                pdec_from_status VARCHAR(50),
                pdec_to_status   VARCHAR(50),
                pdec_remarks     TEXT,
                pdec_amount      NUMERIC(15, 2),
                created_at       TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS purdecisions_pcs_idx ON pur.purdecisions(pdec_pcs_id);
            CREATE INDEX IF NOT EXISTS purdecisions_acc_idx ON pur.purdecisions(pdec_acc_id);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS pur.purdecisions;");
    }
};
