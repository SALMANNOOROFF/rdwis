<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix premature att_locked2 = true on attendance rows where the following month's salary has not been fulfilled.
     * Days 26..31 belong to next month's payroll cycle and must remain unlocked until next month's salary is fulfilled.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE hr.attendance a
            SET att_locked2 = false
            WHERE a.att_startdt >= '2026-09-01'
              AND a.att_locked2 = true
              AND NOT EXISTS (
                  SELECT 1 FROM hr.salreqs srq
                  WHERE srq.srq_emp_id::text = a.att_emp_id::text
                    AND srq.srq_month = (DATE_TRUNC('month', a.att_startdt + INTERVAL '1 month') + INTERVAL '1 month - 1 day')::date
                    AND srq.srq_status IN ('Fulfilled', 'Closed')
              )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op as att_locked2 is dynamically governed by next month's salary fulfillment
    }
};
