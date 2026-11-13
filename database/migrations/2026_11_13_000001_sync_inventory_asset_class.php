<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Inventory / Asset CLASS sync.
 *
 * The Asset / Inventory class of a stored item lives in ina.invats.ias_type2
 * (6 = Asset, 5 = Inventory) and is snapshotted from the purchase case item
 * (pur.purcaseitems.pci_type2) at the time the goods receipt is finalized.
 *
 * If the class of the case item is corrected afterwards, the inventory row keeps
 * the old value and the item keeps showing on the wrong tab (Assets / Inventory).
 * This migration re-aligns every stored item with the class of its case item.
 *
 * The previous values are kept in ina.invats_type2_backup so the change can be
 * rolled back.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS ina.invats_type2_backup (
                ias_id     integer PRIMARY KEY,
                old_type2  smallint NULL,
                new_type2  smallint NULL,
                synced_at  timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Keep what we are about to overwrite.
        DB::statement("
            INSERT INTO ina.invats_type2_backup (ias_id, old_type2, new_type2)
            SELECT i.ias_id, i.ias_type2, p.pci_type2
            FROM ina.invats i
            JOIN pur.purcaseitems p ON p.pci_id = i.ias_pci_id
            WHERE p.pci_type2 IS NOT NULL
              AND i.ias_type2 IS DISTINCT FROM p.pci_type2
            ON CONFLICT (ias_id) DO UPDATE
                SET old_type2 = EXCLUDED.old_type2,
                    new_type2 = EXCLUDED.new_type2,
                    synced_at = CURRENT_TIMESTAMP
        ");

        $updated = DB::update("
            UPDATE ina.invats i
            SET ias_type2 = p.pci_type2
            FROM pur.purcaseitems p
            WHERE p.pci_id = i.ias_pci_id
              AND p.pci_type2 IS NOT NULL
              AND i.ias_type2 IS DISTINCT FROM p.pci_type2
        ");

        echo "  Inventory/Asset class synced for {$updated} stored item(s)." . PHP_EOL;
    }

    public function down(): void
    {
        // Only roll back the rows this migration actually changed and that were
        // not touched again afterwards.
        DB::statement("
            UPDATE ina.invats i
            SET ias_type2 = b.old_type2
            FROM ina.invats_type2_backup b
            WHERE b.ias_id = i.ias_id
              AND i.ias_type2 IS NOT DISTINCT FROM b.new_type2
        ");

        DB::statement("DROP TABLE IF EXISTS ina.invats_type2_backup");
    }
};
