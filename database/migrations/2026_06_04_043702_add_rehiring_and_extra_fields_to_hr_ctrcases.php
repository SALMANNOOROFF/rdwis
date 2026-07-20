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
        Schema::table('hr.ctrcases', function (Blueprint $table) {
            $table->string('ctc_cnic', 15)->nullable();
            $table->string('ctc_contact')->nullable();
            $table->string('ctc_cv_path')->nullable();
            $table->string('ctc_emp_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr.ctrcases', function (Blueprint $table) {
            $table->dropColumn(['ctc_cnic', 'ctc_contact', 'ctc_cv_path', 'ctc_emp_type']);
        });
    }
};
