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
            $table->string('pcs_fulfillment_status')->default('Pending Receipt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pur.purcases', function (Blueprint $table) {
            $table->dropColumn('pcs_fulfillment_status');
        });
    }
};
