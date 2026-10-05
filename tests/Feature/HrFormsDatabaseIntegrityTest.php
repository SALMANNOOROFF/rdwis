<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrFormsDatabaseIntegrityTest extends TestCase
{
    /**
     * Proves that no existing table definition, column, data type, or nullability
     * has changed in any non-hrforms schema (hr, prj, fin, cen, aud, pur, frm, ina, etc.).
     */
    public function test_existing_database_schemas_are_strictly_unmodified(): void
    {
        $snapshotPath = base_path('scratch/baseline_schema_snapshot.json');
        $this->assertFileExists($snapshotPath, 'Baseline schema snapshot file must exist.');

        $baseline = json_decode(file_get_contents($snapshotPath), true);
        $expectedHash = $baseline['md5'];

        $currentRows = DB::select("
            SELECT table_schema, table_name, column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema NOT IN ('pg_catalog', 'information_schema', 'hrforms')
            ORDER BY table_schema, table_name, ordinal_position
        ");

        $currentData = array_map(function($r) {
            return (array) $r;
        }, $currentRows);

        $currentHash = md5(json_encode($currentData));

        $this->assertSame(
            $expectedHash,
            $currentHash,
            'CRITICAL VIOLATION: Existing database schemas were modified! The information_schema hash does not match baseline.'
        );

        $this->assertSame(
            $baseline['total_columns'],
            count($currentRows),
            'CRITICAL VIOLATION: Existing column count has changed.'
        );
    }
}
