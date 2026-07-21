<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Revert fin.docs_ipc view to legacy-only statuses + 'Under Approval'.
     *
     * The previous migration (2026_07_20_000001) added 'With DFinance', 'With MD', etc.
     * to the view's WHERE clause. Since pcs_status now only contains legacy values
     * (with 'Under Approval' covering the entire routing chain), those labels
     * are no longer needed.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW fin.docs_ipc AS
            SELECT 
                pcs_id AS docid, 
                pcs_type AS doctype, 
                pcs_date AS rdate, 
                pcs_title AS title, 
                pcs_effhed_id AS effhed_id, 
                pcs_effunt_id AS effunt_id, 
                pcs_hed_id AS hed_id, 
                pcs_unt_id AS unt_id, 
                pcs_sudohed AS sudohed, 
                pcs_midprice AS amount1, 
                pcs_midtax AS tax1, 
                pcs_price AS amount2, 
                pcs_transtype AS transtype
            FROM pur.purcases
            WHERE pcs_status IN ('Under Scrutiny', 'Under Revision', 'Under Approval')
            UNION ALL
            SELECT 
                sor_id AS docid, 
                sor_type AS doctype, 
                sor_releasedtg AS rdate, 
                'Salary for ' || sor_empnamecomp AS title, 
                sor_effhed_id AS effhed_id, 
                sor_effunt_id AS effunt_id, 
                sor_hed_id AS hed_id, 
                sor_unt_id AS unt_id, 
                sor_sudohed AS sudohed, 
                sor_salary AS amount1, 
                0 AS tax1, 
                sor_salary AS amount2, 
                sor_transtype AS transtype
            FROM hr.salreqs 
            INNER JOIN fin.salorders ON srq_id = sor_srq_id
            WHERE sor_status IN ('Draft') AND srq_status = 'In Process'
        ");
    }

    /**
     * Reverse: restore the view with new-style status labels included.
     */
    public function down(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW fin.docs_ipc AS
            SELECT 
                pcs_id AS docid, 
                pcs_type AS doctype, 
                pcs_date AS rdate, 
                pcs_title AS title, 
                pcs_effhed_id AS effhed_id, 
                pcs_effunt_id AS effunt_id, 
                pcs_hed_id AS hed_id, 
                pcs_unt_id AS unt_id, 
                pcs_sudohed AS sudohed, 
                pcs_midprice AS amount1, 
                pcs_midtax AS tax1, 
                pcs_price AS amount2, 
                pcs_transtype AS transtype
            FROM pur.purcases
            WHERE pcs_status IN (
                'Under Scrutiny', 'Under Revision', 'Under Approval',
                'With DFinance', 'With MD', 'With DDG', 'With DG'
            )
            UNION ALL
            SELECT 
                sor_id AS docid, 
                sor_type AS doctype, 
                sor_releasedtg AS rdate, 
                'Salary for ' || sor_empnamecomp AS title, 
                sor_effhed_id AS effhed_id, 
                sor_effunt_id AS effunt_id, 
                sor_hed_id AS hed_id, 
                sor_unt_id AS unt_id, 
                sor_sudohed AS sudohed, 
                sor_salary AS amount1, 
                0 AS tax1, 
                sor_salary AS amount2, 
                sor_transtype AS transtype
            FROM hr.salreqs 
            INNER JOIN fin.salorders ON srq_id = sor_srq_id
            WHERE sor_status IN ('Draft') AND srq_status = 'In Process'
        ");
    }
};
