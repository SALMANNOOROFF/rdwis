<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * All tables are strictly created inside the separate 'hrforms' schema.
     * ZERO alterations are made to any existing table or schema.
     */
    public function up(): void
    {
        // 1. Create dedicated schema 'hrforms'
        DB::statement('CREATE SCHEMA IF NOT EXISTS hrforms;');

        // 2. hrforms.form_templates
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.form_templates (
                id BIGSERIAL PRIMARY KEY,
                form_code VARCHAR(50) NOT NULL UNIQUE,
                annex VARCHAR(20) NOT NULL,
                title VARCHAR(255) NOT NULL,
                category VARCHAR(50) NOT NULL,
                view_template VARCHAR(100) NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 3. hrforms.form_matrix
        // requirement values: required, not_required, conditional, at_joining, optional, relaxed
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.form_matrix (
                id BIGSERIAL PRIMARY KEY,
                form_code VARCHAR(50) NOT NULL,
                hiring_type VARCHAR(50) NOT NULL,
                requirement VARCHAR(30) NOT NULL,
                condition_rule VARCHAR(100) NULL,
                default_order INT NOT NULL DEFAULT 0,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT uq_hrforms_matrix UNIQUE (form_code, hiring_type)
            );
        ");

        // 4. hrforms.salary_bands
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.salary_bands (
                id BIGSERIAL PRIMARY KEY,
                designation VARCHAR(100) NOT NULL UNIQUE,
                category VARCHAR(50) NOT NULL,
                sub_grades JSONB NULL,
                min_salary NUMERIC(12, 2) NOT NULL,
                max_salary NUMERIC(12, 2) NOT NULL,
                currency VARCHAR(10) NOT NULL DEFAULT 'PKR',
                notes TEXT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 5. hrforms.approval_chains
        // grade_level strictly in: 'ALL', 'RO_AND_ABOVE', 'RT_AND_BELOW'
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.approval_chains (
                id BIGSERIAL PRIMARY KEY,
                form_code VARCHAR(50) NOT NULL,
                grade_level VARCHAR(50) NOT NULL DEFAULT 'ALL' CHECK (grade_level IN ('ALL', 'RO_AND_ABOVE', 'RT_AND_BELOW')),
                sequence INT NOT NULL,
                role_title VARCHAR(100) NOT NULL,
                action_type VARCHAR(50) NOT NULL,
                can_approve BOOLEAN NOT NULL DEFAULT FALSE,
                is_final_authority BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 6. hrforms.project_extras (Logical link to prj.projects.prj_id INT - NO foreign key constraint)
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.project_extras (
                id BIGSERIAL PRIMARY KEY,
                project_id INT NOT NULL UNIQUE,
                work_order_no VARCHAR(150) NULL,
                work_order_date DATE NULL,
                warranty_expiry DATE NULL,
                approved_headcount INT NULL,
                extra_data JSONB NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 7. hrforms.case_extras (Logical link to hr.ctrcases.ctc_id INT - NO foreign key constraint)
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.case_extras (
                id BIGSERIAL PRIMARY KEY,
                case_id INT NOT NULL UNIQUE,
                headcount_in_proposal BOOLEAN NOT NULL DEFAULT FALSE,
                interview_date DATE NULL,
                interview_time VARCHAR(50) NULL,
                interview_venue VARCHAR(200) NULL,
                principal_candidate_name VARCHAR(200) NULL,
                standby_candidate_name VARCHAR(200) NULL,
                shortlisted_candidates JSONB NULL,
                shift_amount NUMERIC(14, 2) NOT NULL DEFAULT 0.00,
                ex_post_facto BOOLEAN NOT NULL DEFAULT FALSE,
                ex_post_facto_justification TEXT NULL,
                single_candidate_mode BOOLEAN NOT NULL DEFAULT TRUE,
                single_candidate_justification TEXT NULL,
                advertisement_exemption BOOLEAN NOT NULL DEFAULT FALSE,
                advertisement_exemption_justification TEXT NULL,
                extra_data JSONB NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 8. hrforms.case_forms (Logical link to hr.ctrcases.ctc_id INT - instance_key + partial unique index)
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.case_forms (
                id BIGSERIAL PRIMARY KEY,
                case_id INT NOT NULL,
                form_code VARCHAR(50) NOT NULL,
                annex VARCHAR(20) NOT NULL,
                instance_key VARCHAR(50) NOT NULL DEFAULT 'main',
                form_title VARCHAR(255) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'Draft',
                is_required BOOLEAN NOT NULL DEFAULT TRUE,
                form_data JSONB NULL,
                snapshot_data JSONB NULL,
                filled_by INT NULL,
                submitted_at TIMESTAMP WITHOUT TIME ZONE NULL,
                submitted_by INT NULL,
                deleted_at TIMESTAMP WITHOUT TIME ZONE NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_case_forms_case_id ON hrforms.case_forms(case_id);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_case_forms_code ON hrforms.case_forms(form_code);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_case_forms_status ON hrforms.case_forms(status);");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS uq_case_forms_instance ON hrforms.case_forms(case_id, form_code, instance_key) WHERE deleted_at IS NULL;");

        // 9. hrforms.audit_log (Insert-only: no update or delete)
        DB::statement("
            CREATE TABLE IF NOT EXISTS hrforms.audit_log (
                id BIGSERIAL PRIMARY KEY,
                case_id INT NULL,
                form_id BIGINT NULL,
                action VARCHAR(50) NOT NULL,
                user_id INT NULL,
                user_name VARCHAR(150) NULL,
                details TEXT NULL,
                old_values JSONB NULL,
                new_values JSONB NULL,
                ip_address VARCHAR(45) NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_audit_log_case_id ON hrforms.audit_log(case_id);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_audit_log_action ON hrforms.audit_log(action);");
    }

    /**
     * Reverse the migrations.
     * Strictly drops hrforms objects only. Existing schemas are untouched.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS hrforms.audit_log CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.case_forms CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.case_extras CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.project_extras CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.approval_chains CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.salary_bands CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.form_matrix CASCADE;');
        DB::statement('DROP TABLE IF EXISTS hrforms.form_templates CASCADE;');
        DB::statement('DROP SCHEMA IF EXISTS hrforms CASCADE;');
    }
};
