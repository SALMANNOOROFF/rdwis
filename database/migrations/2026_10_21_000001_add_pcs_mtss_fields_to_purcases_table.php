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
        Schema::table('pur.purcases', function (Blueprint $table) {
            if (!Schema::hasColumn('pur.purcases', 'pcs_mtss_sent')) {
                $table->boolean('pcs_mtss_sent')->default(false);
            }
            if (!Schema::hasColumn('pur.purcases', 'pcs_mtss_date')) {
                $table->timestamp('pcs_mtss_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pur.purcases', function (Blueprint $table) {
            if (Schema::hasColumn('pur.purcases', 'pcs_mtss_sent')) {
                $table->dropColumn('pcs_mtss_sent');
            }
            if (Schema::hasColumn('pur.purcases', 'pcs_mtss_date')) {
                $table->dropColumn('pcs_mtss_date');
            }
        });
    }
};
