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
            CREATE SCHEMA IF NOT EXISTS gen;

            CREATE TABLE IF NOT EXISTS gen.cases (
                gcs_id                  bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                gcs_file_no             varchar(60) NOT NULL UNIQUE,
                gcs_title               varchar(255) NOT NULL,
                gcs_category            varchar(100) NOT NULL DEFAULT 'General',
                gcs_subject             text NULL,
                gcs_description         text NULL,
                gcs_priority            varchar(30) NOT NULL DEFAULT 'Normal',
                gcs_status              varchar(40) NOT NULL DEFAULT 'Draft',
                gcs_current_stage       varchar(60) NOT NULL DEFAULT 'Initiator',
                gcs_current_holder_id   integer NULL REFERENCES cen.accounts(acc_id) ON DELETE SET NULL,
                gcs_created_by          integer NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
                gcs_unt_id              integer NULL REFERENCES cen.units(unt_id) ON DELETE SET NULL,
                gcs_metadata            jsonb NULL,
                gcs_linkable_type       varchar(150) NULL,
                gcs_linkable_id         bigint NULL,
                created_at              timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at              timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_gen_cases_file_no ON gen.cases (gcs_file_no);
            CREATE INDEX IF NOT EXISTS idx_gen_cases_holder ON gen.cases (gcs_current_holder_id);
            CREATE INDEX IF NOT EXISTS idx_gen_cases_creator ON gen.cases (gcs_created_by);
            CREATE INDEX IF NOT EXISTS idx_gen_cases_status ON gen.cases (gcs_status);
            CREATE INDEX IF NOT EXISTS idx_gen_cases_category ON gen.cases (gcs_category);
            CREATE INDEX IF NOT EXISTS idx_gen_cases_linkable ON gen.cases (gcs_linkable_type, gcs_linkable_id);

            CREATE TABLE IF NOT EXISTS gen.case_notes (
                gcn_id                  bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                gcn_case_id             bigint NOT NULL REFERENCES gen.cases(gcs_id) ON DELETE CASCADE,
                gcn_para_no             integer NOT NULL DEFAULT 1,
                gcn_title               varchar(255) NULL,
                gcn_content             text NOT NULL,
                gcn_author_id           integer NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
                gcn_author_name         varchar(150) NOT NULL,
                gcn_author_rank         varchar(100) NULL,
                gcn_author_desig        varchar(150) NULL,
                created_at              timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at              timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_gen_case_notes_case ON gen.case_notes (gcn_case_id, gcn_para_no);

            CREATE TABLE IF NOT EXISTS gen.case_movements (
                gcm_id                  bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                gcm_case_id             bigint NOT NULL REFERENCES gen.cases(gcs_id) ON DELETE CASCADE,
                gcm_actor_id            integer NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
                gcm_actor_name          varchar(150) NOT NULL,
                gcm_actor_rank          varchar(100) NULL,
                gcm_actor_desig         varchar(150) NULL,
                gcm_action              varchar(100) NOT NULL,
                gcm_from_stage          varchar(60) NULL,
                gcm_to_stage            varchar(60) NULL,
                gcm_from_holder_id      integer NULL REFERENCES cen.accounts(acc_id) ON DELETE SET NULL,
                gcm_to_holder_id        integer NULL REFERENCES cen.accounts(acc_id) ON DELETE SET NULL,
                gcm_remarks             text NULL,
                gcm_dtg                 timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_gen_case_movements_case ON gen.case_movements (gcm_case_id, gcm_dtg DESC);

            CREATE TABLE IF NOT EXISTS gen.case_attachments (
                gca_id                  bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                gca_case_id             bigint NOT NULL REFERENCES gen.cases(gcs_id) ON DELETE CASCADE,
                gca_title               varchar(255) NOT NULL,
                gca_filename            varchar(255) NOT NULL,
                gca_path                varchar(500) NOT NULL,
                gca_size                bigint NULL,
                gca_mime                varchar(100) NULL,
                gca_uploader_id         integer NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
                created_at              timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_gen_case_attachments_case ON gen.case_attachments (gca_case_id);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            DROP TABLE IF EXISTS gen.case_attachments CASCADE;
            DROP TABLE IF EXISTS gen.case_movements CASCADE;
            DROP TABLE IF EXISTS gen.case_notes CASCADE;
            DROP TABLE IF EXISTS gen.cases CASCADE;
        ");
    }
};

