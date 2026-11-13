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
            ALTER TABLE gen.cases ADD COLUMN IF NOT EXISTS gcs_description text NULL;
            ALTER TABLE gen.cases ALTER COLUMN gcs_subject TYPE text;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            ALTER TABLE gen.cases DROP COLUMN IF EXISTS gcs_description;
        ");
    }
};

