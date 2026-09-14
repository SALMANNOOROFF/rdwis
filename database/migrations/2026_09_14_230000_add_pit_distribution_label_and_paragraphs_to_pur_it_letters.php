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
            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'pur' AND table_name = 'pur_it_letters') THEN
                    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'pur' AND table_name = 'pur_it_letters' AND column_name = 'pit_distribution_label') THEN
                        ALTER TABLE pur.pur_it_letters ADD COLUMN pit_distribution_label varchar(255) DEFAULT 'See distribution';
                    END IF;
                    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'pur' AND table_name = 'pur_it_letters' AND column_name = 'pit_paragraphs') THEN
                        ALTER TABLE pur.pur_it_letters ADD COLUMN pit_paragraphs jsonb NULL;
                    END IF;
                END IF;
            END $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
