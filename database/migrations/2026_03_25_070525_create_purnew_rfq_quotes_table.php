<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS purnew.rfq_quotes (
                rq_id        SERIAL PRIMARY KEY,
                rfq_id       INTEGER NOT NULL,
                rfq_item_id  INTEGER NOT NULL,
                frm_id       INTEGER NOT NULL,
                quoted_price NUMERIC NOT NULL DEFAULT 0,
                is_accepted  BOOLEAN NOT NULL DEFAULT FALSE,
                created_at   TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(rfq_id, rfq_item_id, frm_id)
            );
            CREATE INDEX IF NOT EXISTS purnew_rfq_quotes_rfq_idx ON purnew.rfq_quotes(rfq_id);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS purnew.rfq_quotes;");
    }
};
