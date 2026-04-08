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
            CREATE TABLE IF NOT EXISTS pur.purnotifications (
                pnt_id          SERIAL PRIMARY KEY,
                pnt_acc_id      INTEGER NOT NULL,
                pnt_pcs_id      INTEGER NOT NULL,
                pnt_message     TEXT NOT NULL,
                pnt_is_read     BOOLEAN DEFAULT FALSE,
                created_at      TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS purnotifications_acc_idx ON pur.purnotifications(pnt_acc_id);
            CREATE INDEX IF NOT EXISTS purnotifications_read_idx ON pur.purnotifications(pnt_acc_id, pnt_is_read);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS pur.purnotifications;");
    }
};
