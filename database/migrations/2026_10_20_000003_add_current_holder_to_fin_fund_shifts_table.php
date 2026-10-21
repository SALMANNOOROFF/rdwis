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
        Schema::table('fin.fund_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('fin.fund_shifts', 'fsh_current_acc_id')) {
                $table->integer('fsh_current_acc_id')->nullable()->index();
            }
            if (!Schema::hasColumn('fin.fund_shifts', 'fsh_current_holder')) {
                $table->string('fsh_current_holder', 150)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fin.fund_shifts', function (Blueprint $table) {
            $table->dropColumn(['fsh_current_acc_id', 'fsh_current_holder']);
        });
    }
};
