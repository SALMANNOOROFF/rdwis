<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: Tables aud.revs, aud.revcomps, and aud.revdata already exist in production
     * imported via raw dump. This migration documents the existing schema and is fully
     * idempotent (IF NOT EXISTS) to ensure existing live data is never modified or truncated.
     */
    public function up(): void
    {
        DB::statement("CREATE SCHEMA IF NOT EXISTS aud;");

        DB::statement("
            CREATE TABLE IF NOT EXISTS aud.revs (
                rev_id SERIAL PRIMARY KEY,
                rev_obj VARCHAR NOT NULL,
                rev_releasedtg TIMESTAMP WITHOUT TIME ZONE,
                rev_closedtg TIMESTAMP WITHOUT TIME ZONE,
                rev_objid VARCHAR NOT NULL,
                rev_reason VARCHAR,
                rev_status VARCHAR NOT NULL DEFAULT 'Draft',
                rev_unt_id INTEGER NOT NULL,
                rev_date DATE NOT NULL,
                rev_type SMALLINT NOT NULL,
                rev_intunt_id INTEGER NOT NULL,
                rev_ref VARCHAR,
                rev_objext VARCHAR
            );
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS aud.revcomps (
                rvc_id SERIAL PRIMARY KEY,
                rvc_rev_id INTEGER NOT NULL,
                rvc_table VARCHAR NOT NULL,
                rvc_detail TEXT NOT NULL,
                rvc_rowid VARCHAR NOT NULL,
                rvc_action VARCHAR NOT NULL,
                rvc_type SMALLINT
            );
        ");

        DB::statement("
            CREATE TABLE IF NOT EXISTS aud.revdata (
                rvd_id SERIAL PRIMARY KEY,
                rvd_rev_id INTEGER NOT NULL,
                rvd_table VARCHAR NOT NULL,
                rvd_rowid VARCHAR NOT NULL,
                rvd_attrib VARCHAR NOT NULL,
                rvd_oldvalue VARCHAR,
                rvd_newvalue VARCHAR,
                rvd_datatype VARCHAR NOT NULL,
                rvd_type SMALLINT NOT NULL,
                rvd_conversion CHAR(1),
                rvd_colname VARCHAR,
                rvd_alias VARCHAR
            );
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_revs_status ON aud.revs (rev_status);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_revcomps_rev_id ON aud.revcomps (rvc_rev_id);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_revdata_rev_id ON aud.revdata (rvd_rev_id);");
    }

    /**
     * Reverse the migrations.
     *
     * Safely drops the 3 performance indexes created in up().
     * Live data tables (aud.revs, aud.revcomps, aud.revdata) are NEVER dropped.
     */
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS aud.idx_revdata_rev_id;");
        DB::statement("DROP INDEX IF EXISTS aud.idx_revcomps_rev_id;");
        DB::statement("DROP INDEX IF EXISTS aud.idx_revs_status;");
    }
};
