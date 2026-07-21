<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create the pur.purcase_substatus table for tracking
     * which authority currently holds a purchase case.
     *
     * This separates the routing workflow (Division → DFinance → MD → DDG → DG)
     * from the legacy pcs_status column, which only carries milestone states
     * (Draft, Under Approval, Approved, etc.)
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS pur.purcase_substatus (
                pss_id          integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                pss_pcs_id      integer NOT NULL REFERENCES pur.purcases(pcs_id),
                pss_stage       varchar(30) NOT NULL,
                pss_is_current  boolean NOT NULL DEFAULT true,
                pss_since       timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
                pss_until       timestamp WITHOUT TIME ZONE NULL
            );

            -- Only one current substatus row per case (partial unique index)
            CREATE UNIQUE INDEX IF NOT EXISTS uq_purcase_substatus_current
                ON pur.purcase_substatus (pss_pcs_id) WHERE pss_is_current;

            -- General lookup index
            CREATE INDEX IF NOT EXISTS idx_purcase_substatus_pcs
                ON pur.purcase_substatus (pss_pcs_id);

            COMMENT ON TABLE pur.purcase_substatus IS 'Tracks which authority currently holds a purchase case. Stages: Division, DFinance, MD, DDG, DG. DProc is collaborative and never appears here.';
            COMMENT ON COLUMN pur.purcase_substatus.pss_stage IS 'Current routing stage: Division, DFinance, MD, DDG, DG';
            COMMENT ON COLUMN pur.purcase_substatus.pss_is_current IS 'True for the single active row per case. False for historical rows.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS pur.purcase_substatus;");
    }
};
