<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates hrforms.case_milestones table to track Annex C progress tracker milestones.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.case_milestones (
                id BIGSERIAL PRIMARY KEY,
                case_id INT NOT NULL,
                step_code VARCHAR(50) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'Pending',
                event_date DATE NULL,
                note TEXT NULL,
                updated_by INT NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT hrforms_case_milestones_case_step_unique UNIQUE (case_id, step_code)
            );
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS hrforms_case_milestones_case_id_idx 
            ON hrforms.case_milestones (case_id);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS hrforms.case_milestones;");
    }
};
