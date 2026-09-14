<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Backfill pur.purcases_shd for all existing cases missing a record
        $casesMissingShd = DB::table('pur.purcases as c')
            ->leftJoin('pur.purcases_shd as shd', 'shd.pcd_pcs_id', '=', 'c.pcs_id')
            ->whereNull('shd.pcd_pcs_id')
            ->select('c.pcs_id', 'c.pcs_type')
            ->get();

        foreach ($casesMissingShd as $case) {
            $type = strtoupper(trim((string)$case->pcs_type));
            
            // Check if any line items have a subhead already
            $itemSubhead = DB::table('pur.purcaseitems')
                ->where('pci_pcs_id', $case->pcs_id)
                ->whereNotNull('pci_subhead')
                ->where('pci_subhead', '!=', '')
                ->value('pci_subhead');

            if (!empty($itemSubhead)) {
                $subhead = $itemSubhead;
            } elseif ($type === 'PS') {
                $subhead = 'Equipment';
            } else {
                $subhead = 'Misc';
            }

            DB::table('pur.purcases_shd')->updateOrInsert(
                [
                    'pcd_pcs_id' => $case->pcs_id,
                    'pcd_subhead' => $subhead,
                ],
                [
                    'pcd_type' => $case->pcs_type ?: ($type === 'PS' ? 'Ps' : ($type === 'PT' ? 'Pt' : 'Rb')),
                    'pcd_ratio' => 1.0,
                ]
            );
        }

        // 2. Explicitly ensure PS-2963 and PS-2144 have Equipment in pur.purcases_shd
        foreach ([2963, 2144] as $pcsId) {
            $c = DB::table('pur.purcases')->where('pcs_id', $pcsId)->first();
            if ($c) {
                DB::table('pur.purcases_shd')->updateOrInsert(
                    [
                        'pcd_pcs_id' => $pcsId,
                        'pcd_subhead' => 'Equipment',
                    ],
                    [
                        'pcd_type' => $c->pcs_type ?: 'Ps',
                        'pcd_ratio' => 1.0,
                    ]
                );
            }
        }

        // 3. Backfill pur.purcaseitems.pci_subhead where null or empty
        $itemsMissingSubhead = DB::table('pur.purcaseitems as i')
            ->join('pur.purcases as c', 'c.pcs_id', '=', 'i.pci_pcs_id')
            ->where(function($q) {
                $q->whereNull('i.pci_subhead')->orWhere('i.pci_subhead', '=', '');
            })
            ->select('i.pci_id', 'i.pci_pcs_id', 'c.pcs_type')
            ->get();

        foreach ($itemsMissingSubhead as $it) {
            $caseSubhead = DB::table('pur.purcases_shd')
                ->where('pcd_pcs_id', $it->pci_pcs_id)
                ->value('pcd_subhead');

            if (empty($caseSubhead)) {
                $caseSubhead = strtoupper(trim((string)$it->pcs_type)) === 'PS' ? 'Equipment' : 'Misc';
            }

            DB::table('pur.purcaseitems')
                ->where('pci_id', $it->pci_id)
                ->update([
                    'pci_subhead' => $caseSubhead,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as this is a non-destructive data backfill
    }
};
