<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE purnew.rfq ADD COLUMN IF NOT EXISTS pcs_title varchar');
        DB::statement("
            UPDATE purnew.rfq r
            SET pcs_title = (
                SELECT p.pcs_title
                FROM pur.purcases p
                WHERE p.pcs_unt_id = r.pcs_unt_id
                  AND (p.pcs_hed_id = r.pcs_hed_id OR r.pcs_hed_id IS NULL)
                ORDER BY p.pcs_id DESC
                LIMIT 1
            )
            WHERE r.pcs_title IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE purnew.rfq DROP COLUMN IF EXISTS pcs_title');
    }
};
