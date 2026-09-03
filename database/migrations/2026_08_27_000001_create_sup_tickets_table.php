<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create the sup schema, sup.tickets, and sup.ticket_activities tables.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE SCHEMA IF NOT EXISTS sup;

            CREATE TABLE IF NOT EXISTS sup.tickets (
                tkt_id              integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                tkt_ref             varchar(30) NOT NULL UNIQUE,
                tkt_type            varchar(30) NOT NULL DEFAULT 'Complaint',
                tkt_module          varchar(60) NOT NULL DEFAULT 'General',
                tkt_subject         varchar(255) NOT NULL,
                tkt_description     text NOT NULL,
                tkt_priority        varchar(20) NOT NULL DEFAULT 'Normal',
                tkt_status          varchar(30) NOT NULL DEFAULT 'Open',
                tkt_user_id         integer NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE CASCADE,
                tkt_user_name       varchar(200) NOT NULL,
                tkt_user_role       varchar(60) NOT NULL,
                tkt_unt_id          integer NULL REFERENCES cen.units(unt_id) ON DELETE SET NULL,
                tkt_unt_name        varchar(200) NULL,
                tkt_is_apex         boolean NOT NULL DEFAULT false,
                tkt_attachment      varchar(500) NULL,
                tkt_solved_by       integer NULL REFERENCES cen.accounts(acc_id) ON DELETE SET NULL,
                tkt_solved_by_name  varchar(200) NULL,
                tkt_solved_at       timestamp WITHOUT TIME ZONE NULL,
                tkt_resolution_note text NULL,
                tkt_created_at      timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
                tkt_updated_at      timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_tickets_user ON sup.tickets (tkt_user_id);
            CREATE INDEX IF NOT EXISTS idx_tickets_status ON sup.tickets (tkt_status);
            CREATE INDEX IF NOT EXISTS idx_tickets_module ON sup.tickets (tkt_module);
            CREATE INDEX IF NOT EXISTS idx_tickets_apex ON sup.tickets (tkt_is_apex);
            CREATE INDEX IF NOT EXISTS idx_tickets_created ON sup.tickets (tkt_created_at DESC);

            CREATE TABLE IF NOT EXISTS sup.ticket_activities (
                act_id          integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                act_tkt_id      integer NOT NULL REFERENCES sup.tickets(tkt_id) ON DELETE CASCADE,
                act_user_id     integer NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE CASCADE,
                act_user_name   varchar(200) NOT NULL,
                act_user_role   varchar(60) NOT NULL,
                act_action      varchar(40) NOT NULL,
                act_message     text NOT NULL,
                act_attachment  varchar(500) NULL,
                act_created_at  timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_ticket_activities_tkt ON sup.ticket_activities (act_tkt_id);
            CREATE INDEX IF NOT EXISTS idx_ticket_activities_created ON sup.ticket_activities (act_created_at ASC);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            DROP TABLE IF EXISTS sup.ticket_activities CASCADE;
            DROP TABLE IF EXISTS sup.tickets CASCADE;
        ");
    }
};
