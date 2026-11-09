<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Transfer Mudassir Muttaqi PN (19-25-12-6183), Director Procurement & Inventory,
     * from HR Department (820000) to Inventory and Procurement Department (810000).
     */
    public function up(): void
    {
        DB::table('hr.emps')
            ->where('emp_id', '19-25-12-6183')
            ->update(['emp_unt_id' => 810000]);

        DB::table('hr.contracts')
            ->where('ctr_num', '19-25-12-6183')
            ->update(['ctr_unt_id' => 810000]);

        DB::table('hr.attendance')
            ->where('att_emp_id', '19-25-12-6183')
            ->update(['att_unt_id' => 810000]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hr.emps')
            ->where('emp_id', '19-25-12-6183')
            ->update(['emp_unt_id' => 820000]);

        DB::table('hr.contracts')
            ->where('ctr_num', '19-25-12-6183')
            ->update(['ctr_unt_id' => 820000]);

        DB::table('hr.attendance')
            ->where('att_emp_id', '19-25-12-6183')
            ->update(['att_unt_id' => 820000]);
    }
};
