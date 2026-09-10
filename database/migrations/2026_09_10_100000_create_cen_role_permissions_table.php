<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS cen.role_permissions (
                id BIGSERIAL PRIMARY KEY,
                role_slug VARCHAR(64) NOT NULL,
                permission VARCHAR(100) NOT NULL,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT uq_cen_role_permission UNIQUE (role_slug, permission)
            );
        ");

        DB::statement("CREATE INDEX IF NOT EXISTS idx_cen_role_permissions_slug ON cen.role_permissions (role_slug);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS cen.role_permissions;");
    }
};
