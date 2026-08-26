<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the hr.ctrcase_substatus table for tracking
     * which authority currently holds a contract case.
     *
     * This separates the routing workflow (Division → HR → Finance → MD → DDG → DG)
     * from the legacy ctc_status column, which carries milestone/legacy states
     * (Draft, Under HR Scrutiny, Under Finance Scrutiny, Under Approval, Approved, Fulfilled, Not Approved, Cancelled, Under Revision)
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS hr.ctrcase_substatus (
                css_id          integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                css_ctc_id      integer NOT NULL REFERENCES hr.ctrcases(ctc_id) ON DELETE CASCADE,
                css_stage       varchar(30) NOT NULL,
                css_is_current  boolean NOT NULL DEFAULT true,
                css_since       timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
                css_until       timestamp WITHOUT TIME ZONE NULL
            );

            -- Only one current substatus row per contract case
            CREATE UNIQUE INDEX IF NOT EXISTS uq_ctrcase_substatus_current
                ON hr.ctrcase_substatus (css_ctc_id) WHERE css_is_current;

            -- General lookup index
            CREATE INDEX IF NOT EXISTS idx_ctrcase_substatus_ctc
                ON hr.ctrcase_substatus (css_ctc_id);

            COMMENT ON TABLE hr.ctrcase_substatus IS 'Tracks which authority currently holds a contract case. Stages: Division, HR, Finance, MD, DDG, DG, Approved, Fulfilled, Not Approved, Cancelled';
            COMMENT ON COLUMN hr.ctrcase_substatus.css_stage IS 'Current routing stage / holder';
            COMMENT ON COLUMN hr.ctrcase_substatus.css_is_current IS 'True for the single active row per case. False for historical rows.';
        ");

        // Ensure extra helper columns exist in hr.ctrcases if needed
        if (!Schema::hasColumn('hr.ctrcases', 'ctc_divisionid')) {
            DB::statement("ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_divisionid integer NULL");
        }
        if (!Schema::hasColumn('hr.ctrcases', 'ctc_createdby')) {
            DB::statement("ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_createdby integer NULL");
        }
        if (!Schema::hasColumn('hr.ctrcases', 'ctc_releasedby')) {
            DB::statement("ALTER TABLE hr.ctrcases ADD COLUMN IF NOT EXISTS ctc_releasedby integer NULL");
        }

        // Seed existing contract cases into hr.ctrcase_substatus
        $cases = DB::table('hr.ctrcases')->select('ctc_id', 'ctc_status', 'ctc_date', 'ctc_releasedtg', 'ctc_closedtg')->get();

        foreach ($cases as $case) {
            $status = trim((string)($case->ctc_status ?? 'Draft'));
            $stage = 'Division';
            $isCurrent = true;
            $since = $case->ctc_date ? \Carbon\Carbon::parse($case->ctc_date) : now();
            $until = null;

            switch ($status) {
                case 'Draft':
                case 'Under Revision':
                    $stage = 'Division';
                    break;
                case 'Under HR Scrutiny':
                    $stage = 'HR';
                    break;
                case 'Under Finance Scrutiny':
                    $stage = 'Finance';
                    break;
                case 'Under Approval':
                    $stage = 'MD';
                    break;
                case 'Approved':
                    $stage = 'Approved';
                    break;
                case 'Fulfilled':
                    $stage = 'Fulfilled';
                    $isCurrent = true;
                    $until = $case->ctc_closedtg ? \Carbon\Carbon::parse($case->ctc_closedtg) : now();
                    break;
                case 'Not Approved':
                case 'Rejected':
                    $stage = 'Not Approved';
                    $isCurrent = true;
                    $until = $case->ctc_closedtg ? \Carbon\Carbon::parse($case->ctc_closedtg) : now();
                    break;
                case 'Cancelled':
                case 'Closed':
                    $stage = 'Cancelled';
                    $isCurrent = true;
                    $until = $case->ctc_closedtg ? \Carbon\Carbon::parse($case->ctc_closedtg) : now();
                    break;
                default:
                    $stage = 'Division';
                    break;
            }

            $exists = DB::table('hr.ctrcase_substatus')->where('css_ctc_id', $case->ctc_id)->exists();
            if (!$exists) {
                DB::table('hr.ctrcase_substatus')->insert([
                    'css_ctc_id'     => $case->ctc_id,
                    'css_stage'      => $stage,
                    'css_is_current' => $isCurrent,
                    'css_since'      => $since,
                    'css_until'      => $until,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TABLE IF EXISTS hr.ctrcase_substatus;");
    }
};
