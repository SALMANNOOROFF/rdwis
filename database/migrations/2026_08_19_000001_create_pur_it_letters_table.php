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
            CREATE TABLE IF NOT EXISTS pur.pur_it_letters (
                pit_id              integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                pit_pcs_id          integer NOT NULL REFERENCES pur.purcases(pcs_id) ON DELETE CASCADE,
                pit_refno           varchar(255) NULL,
                pit_date            varchar(100) NULL,
                pit_subject         varchar(255) NULL,
                pit_para1           text NULL,
                pit_para2           text NULL,
                pit_para3           text NULL,
                pit_signatory_name  varchar(255) NULL,
                pit_signatory_rank  varchar(255) NULL,
                pit_signatory_dept  varchar(255) NULL,
                pit_firms           jsonb NULL,
                pit_items           jsonb NULL,
                created_at          timestamp WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at          timestamp WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );

            CREATE UNIQUE INDEX IF NOT EXISTS uq_pur_it_letters_pcs_id
                ON pur.pur_it_letters (pit_pcs_id);

            CREATE INDEX IF NOT EXISTS idx_pur_it_letters_pcs_id
                ON pur.pur_it_letters (pit_pcs_id);

            COMMENT ON TABLE pur.pur_it_letters IS 'Stores customized Request for Quotation (RFQ) letter and IT Annex data per purchase case.';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS pur.pur_it_letters;");
    }
};
