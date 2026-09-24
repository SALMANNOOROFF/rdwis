<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: Idempotent migration - protects 680 live production rows if table already exists.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fin.contractsverif')) {
            Schema::create('fin.contractsverif', function (Blueprint $table) {
                $table->integer('cvf_ctr_id')->primary();
                $table->boolean('cvf_verif')->default(false);
                $table->timestamp('cvf_dtg')->nullable();

                $table->foreign('cvf_ctr_id')
                    ->references('ctr_id')
                    ->on('hr.contracts')
                    ->onDelete('cascade');
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
            Schema::dropIfExists('fin.contractsverif');
        }
    }
};
