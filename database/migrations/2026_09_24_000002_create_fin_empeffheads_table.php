<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: Idempotent migration - protects 254 live production rows if table already exists.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fin.empeffheads')) {
            Schema::create('fin.empeffheads', function (Blueprint $table) {
                $table->string('eeh_emp_id')->primary();
                $table->integer('eeh_emphed_id')->nullable();
                $table->timestamp('eeh_dtg')->nullable();
                $table->string('eeh_status')->default('Open');
                $table->string('eeh_remarks')->nullable();
                $table->string('eeh_sudohed', 7)->nullable();

                $table->foreign('eeh_emp_id')
                    ->references('emp_id')
                    ->on('hr.emps')
                    ->onDelete('cascade');

                $table->foreign('eeh_emphed_id')
                    ->references('hed_id')
                    ->on('cen.heads')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Guard against dropping production table with live data
        if (app()->environment('testing')) {
            Schema::dropIfExists('fin.empeffheads');
        }
    }
};
