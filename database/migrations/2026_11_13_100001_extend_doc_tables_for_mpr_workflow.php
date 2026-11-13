<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MPR workflow: bring the legacy prj_prghistory capabilities into the doc.* engine.
 *
 *  - doc_period / doc_seq      : the monthly cycle, and the 2nd (3rd ...) MPR of the same month
 *  - doc_stage                 : ProjectOfficer | SORD | MD | Accepted
 *  - doc_is_legacy / legacy_*  : rows migrated from prj.prghistory
 *  - *_name columns            : legacy actors are designations (DSensors, DR&D ...), not accounts,
 *                                so creator_id / action_by become nullable and the name is kept.
 *  - version stage / action    : who did what at which stage (created, edited, forwarded, returned...)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE doc.documents
                ADD COLUMN IF NOT EXISTS doc_period        varchar(10),
                ADD COLUMN IF NOT EXISTS doc_period_label  varchar(20),
                ADD COLUMN IF NOT EXISTS doc_seq           smallint NOT NULL DEFAULT 1,
                ADD COLUMN IF NOT EXISTS doc_stage         varchar(30),
                ADD COLUMN IF NOT EXISTS doc_unt_id        integer,
                ADD COLUMN IF NOT EXISTS doc_is_legacy     boolean NOT NULL DEFAULT false,
                ADD COLUMN IF NOT EXISTS doc_legacy_group  varchar(20),
                ADD COLUMN IF NOT EXISTS doc_creator_name  varchar(120),
                ADD COLUMN IF NOT EXISTS doc_owner_name    varchar(120),
                ADD COLUMN IF NOT EXISTS doc_finalized_at  timestamp WITHOUT TIME ZONE
        ");

        DB::statement("ALTER TABLE doc.documents ALTER COLUMN creator_id DROP NOT NULL");

        DB::statement("
            ALTER TABLE doc.document_versions
                ADD COLUMN IF NOT EXISTS ver_stage       varchar(30),
                ADD COLUMN IF NOT EXISTS ver_action      varchar(40),
                ADD COLUMN IF NOT EXISTS ver_status      varchar(40),
                ADD COLUMN IF NOT EXISTS ver_author_name varchar(120),
                ADD COLUMN IF NOT EXISTS ver_is_legacy   boolean NOT NULL DEFAULT false
        ");

        DB::statement("ALTER TABLE doc.document_versions ALTER COLUMN action_by DROP NOT NULL");

        DB::statement("
            ALTER TABLE doc.document_history
                ADD COLUMN IF NOT EXISTS from_name varchar(120),
                ADD COLUMN IF NOT EXISTS to_name   varchar(120),
                ADD COLUMN IF NOT EXISTS stage     varchar(30)
        ");

        // Existing (non legacy) rows: give them a period and a stage so the new screens
        // can treat every document the same way.
        DB::statement("
            UPDATE doc.documents
            SET doc_period = COALESCE(doc_period, to_char(COALESCE(created_at, now()), 'YYYY-MM')),
                doc_period_label = COALESCE(doc_period_label, to_char(COALESCE(created_at, now()), 'Mon YY')),
                doc_stage = COALESCE(doc_stage, CASE
                    WHEN status IN ('Finalized', 'Accepted', 'Closed') THEN 'Accepted'
                    WHEN status IN ('Forwarded to MD', 'Pending MD') THEN 'MD'
                    WHEN status IN ('Pending Review', 'Submitted to SORD', 'Under Review by SORD') THEN 'SORD'
                    ELSE 'ProjectOfficer'
                END)
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_doc_documents_prj_period ON doc.documents (prj_id, doc_period, doc_seq)
        ");
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_doc_documents_stage ON doc.documents (doc_stage, status)
        ");
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_doc_versions_doc ON doc.document_versions (doc_id, ver_id)
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS doc.idx_doc_documents_prj_period");
        DB::statement("DROP INDEX IF EXISTS doc.idx_doc_documents_stage");
        DB::statement("DROP INDEX IF EXISTS doc.idx_doc_versions_doc");

        DB::statement("
            ALTER TABLE doc.document_history
                DROP COLUMN IF EXISTS from_name,
                DROP COLUMN IF EXISTS to_name,
                DROP COLUMN IF EXISTS stage
        ");

        DB::statement("
            ALTER TABLE doc.document_versions
                DROP COLUMN IF EXISTS ver_stage,
                DROP COLUMN IF EXISTS ver_action,
                DROP COLUMN IF EXISTS ver_status,
                DROP COLUMN IF EXISTS ver_author_name,
                DROP COLUMN IF EXISTS ver_is_legacy
        ");

        DB::statement("
            ALTER TABLE doc.documents
                DROP COLUMN IF EXISTS doc_period,
                DROP COLUMN IF EXISTS doc_period_label,
                DROP COLUMN IF EXISTS doc_seq,
                DROP COLUMN IF EXISTS doc_stage,
                DROP COLUMN IF EXISTS doc_unt_id,
                DROP COLUMN IF EXISTS doc_is_legacy,
                DROP COLUMN IF EXISTS doc_legacy_group,
                DROP COLUMN IF EXISTS doc_creator_name,
                DROP COLUMN IF EXISTS doc_owner_name,
                DROP COLUMN IF EXISTS doc_finalized_at
        ");

        // creator_id / action_by stay nullable: legacy rows may still be present.
    }
};
