<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pur.pur_it_template')) {
            Schema::table('pur.pur_it_template', function (Blueprint $table) {
                if (!Schema::hasColumn('pur.pur_it_template', 'page_setup')) {
                    $table->jsonb('page_setup')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pur.pur_it_template')) {
            Schema::table('pur.pur_it_template', function (Blueprint $table) {
                if (Schema::hasColumn('pur.pur_it_template', 'page_setup')) {
                    $table->dropColumn('page_setup');
                }
            });
        }
    }
};
