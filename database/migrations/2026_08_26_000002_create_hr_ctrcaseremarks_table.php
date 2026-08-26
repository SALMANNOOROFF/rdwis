<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create hr.ctrcaseremarks table for audit trail of approval remarks.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS hr.ctrcaseremarks (
                crr_id          integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                crr_ctc_id      integer NOT NULL REFERENCES hr.ctrcases(ctc_id) ON DELETE CASCADE,
                crr_username    varchar(100) NULL,
                crr_user_rank   varchar(50) NULL,
                crr_user_desig  varchar(100) NULL,
                crr_remarks     text NOT NULL,
                crr_status      varchar(50) NOT NULL,
                crr_dtg         timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_ctrcaseremarks_ctc
                ON hr.ctrcaseremarks (crr_ctc_id);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS hr.ctrcaseremarks;");
    }
};
