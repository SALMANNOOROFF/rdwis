<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pur.purcaseitems', function (Blueprint $table) {
            $table->string('pci_emp_id', 13)->nullable()->after('pci_pcs_id');
            $table->foreign('pci_emp_id')
                ->references('emp_id')
                ->on('hr.emps')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pur.purcaseitems', function (Blueprint $table) {
            $table->dropForeign(['pci_emp_id']);
            $table->dropColumn('pci_emp_id');
        });
    }
};
