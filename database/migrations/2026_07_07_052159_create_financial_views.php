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

        DB::statement("
            CREATE OR REPLACE VIEW fin.docs_shd AS
            SELECT 
                sod_sor_id AS doc_id, 
                sod_type AS doc_type, 
                sod_subhead AS subhead, 
                sod_ratio AS ratio 
            FROM fin.salorders_shd
            UNION ALL
            SELECT 
                pcd_pcs_id AS doc_id, 
                pcd_type AS doc_type, 
                pcd_subhead AS subhead, 
                pcd_ratio AS ratio 
            FROM pur.purcases_shd
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS fin.docs_shd");
        DB::statement("DROP VIEW IF EXISTS fin.docs_ipc");
    }
};

