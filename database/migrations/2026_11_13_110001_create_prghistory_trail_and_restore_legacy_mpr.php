<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MPR goes back to the legacy engine: prj.prghistory stays exactly as it was
 * (same table, same columns, same statuses). The only addition is a separate
 * movement trail, so every hand-over, return and its remarks are preserved
 * instead of being squeezed into the pgh_trail string.
 *
 *  1. prj.prghistory_trail  (new)
 *  2. trail backfilled from the existing pgh_trail of every old MPR
 *  3. the handful of MPRs that were raised on the doc.* engine are written back
 *     into prj.prghistory, so the legacy table holds everything again
 *  4. the legacy copies that were mirrored into doc.* are removed (prj.prghistory
 *     is the source of truth; nothing is lost)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // 1. The trail table
        // ---------------------------------------------------------------
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS prj.prghistory_trail (
                pgt_id             bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                pgt_pgh_id         integer NULL REFERENCES prj.prghistory(pgh_id) ON DELETE CASCADE,
                pgt_prj_id         integer NOT NULL,
                pgt_group          varchar(20) NULL,
                pgt_action         varchar(30) NOT NULL,
                pgt_from_level     integer NULL,
                pgt_from_desig     varchar(60) NULL,
                pgt_from_acc_id    integer NULL,
                pgt_to_level       integer NULL,
                pgt_to_desig       varchar(60) NULL,
                pgt_to_acc_id      integer NULL,
                pgt_status_before  varchar(40) NULL,
                pgt_status_after   varchar(40) NULL,
                pgt_remarks        text NULL,
                pgt_dtg            timestamp WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX IF NOT EXISTS idx_prg_trail_pgh ON prj.prghistory_trail (pgt_pgh_id, pgt_dtg);
            CREATE INDEX IF NOT EXISTS idx_prg_trail_prj ON prj.prghistory_trail (pgt_prj_id, pgt_group);
        ");

        // ---------------------------------------------------------------
        // 2. Backfill the trail of every MPR already in prj.prghistory
        // ---------------------------------------------------------------
        if (DB::table('prj.prghistory_trail')->count() === 0) {
            $levelDesig = $this->levelDesignations();
            $rows = DB::table('prj.prghistory')->orderBy('pgh_id')->get();
            $batch = [];

            foreach ($rows as $row) {
                $hops = array_values(array_filter(explode('-', (string) $row->pgh_trail), fn ($p) => trim($p) !== ''));
                $dtg = $row->pgh_dtg ?: now();

                if (empty($hops)) {
                    $hops = [(string) $row->pgh_level];
                }

                $prev = null;
                foreach ($hops as $i => $hop) {
                    $lvl = (int) $hop;
                    $batch[] = [
                        'pgt_pgh_id'        => $row->pgh_id,
                        'pgt_prj_id'        => $row->pgh_xprj_id,
                        'pgt_group'         => $row->pgh_group,
                        'pgt_action'        => $i === 0 ? 'Created' : 'Forwarded',
                        'pgt_from_level'    => $prev,
                        'pgt_from_desig'    => $prev !== null ? ($levelDesig[$prev] ?? null) : null,
                        'pgt_from_acc_id'   => null,
                        'pgt_to_level'      => $lvl,
                        'pgt_to_desig'      => $levelDesig[$lvl] ?? ($i === 0 ? $row->pgh_author : null),
                        'pgt_to_acc_id'     => null,
                        'pgt_status_before' => null,
                        'pgt_status_after'  => $i === count($hops) - 1 ? $row->pgh_status : 'Under Finalization',
                        'pgt_remarks'       => null,
                        'pgt_dtg'           => $dtg,
                    ];
                    $prev = $lvl;
                }

                // A closed row (Finalized / Cancelled / Edited) is its own last movement.
                if (in_array(strtolower(trim((string) $row->pgh_status)), ['finalized', 'cancelled', 'edited'], true)) {
                    $batch[] = [
                        'pgt_pgh_id'        => $row->pgh_id,
                        'pgt_prj_id'        => $row->pgh_xprj_id,
                        'pgt_group'         => $row->pgh_group,
                        'pgt_action'        => ucfirst(strtolower(trim((string) $row->pgh_status))),
                        'pgt_from_level'    => $row->pgh_level,
                        'pgt_from_desig'    => $levelDesig[(int) $row->pgh_level] ?? $row->pgh_author,
                        'pgt_from_acc_id'   => null,
                        'pgt_to_level'      => $row->pgh_level,
                        'pgt_to_desig'      => $levelDesig[(int) $row->pgh_level] ?? $row->pgh_author,
                        'pgt_to_acc_id'     => null,
                        'pgt_status_before' => null,
                        'pgt_status_after'  => $row->pgh_status,
                        'pgt_remarks'       => null,
                        'pgt_dtg'           => $row->pgh_closedtg ?: $dtg,
                    ];
                }

                if (count($batch) >= 500) {
                    DB::table('prj.prghistory_trail')->insert($batch);
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                DB::table('prj.prghistory_trail')->insert($batch);
            }

            echo '  Trail backfilled: ' . DB::table('prj.prghistory_trail')->count() . ' movements.' . PHP_EOL;
        }

        // ---------------------------------------------------------------
        // 3. Bring the doc.* MPRs back into prj.prghistory
        // ---------------------------------------------------------------
        if (DB::getSchemaBuilder()->hasTable('doc.documents')) {
            $docs = DB::table('doc.documents')
                ->where('doc_type', 'MPR')
                ->where(function ($q) {
                    $q->whereNull('doc_is_legacy')->orWhere('doc_is_legacy', false);
                })
                ->get();

            $moved = 0;
            foreach ($docs as $doc) {
                $versions = DB::table('doc.document_versions')->where('doc_id', $doc->doc_id)->orderBy('ver_id')->get();
                if ($versions->isEmpty()) {
                    continue;
                }

                $last = $versions->last();
                $content = is_string($last->content_data) ? json_decode($last->content_data, true) : (array) $last->content_data;
                $text = trim((string) ($content['mpr_content'] ?? ($content['progress'] ?? '')));

                $creator = DB::table('cen.accounts')->where('acc_id', $doc->creator_id)->first();
                $group = $doc->doc_period
                    ? Carbon::parse($doc->doc_period . '-01')->format('M y')
                    : Carbon::parse($doc->created_at ?: now())->format('M y');

                // Already there? (the same project + group + text)
                $exists = DB::table('prj.prghistory')
                    ->where('pgh_xprj_id', $doc->prj_id)
                    ->where('pgh_group', $group)
                    ->where('pgh_progress', $text)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $status = match (strtolower(trim((string) $doc->status))) {
                    'finalized', 'accepted' => 'Finalized',
                    'returned'              => 'Under Review',
                    'cancelled'             => 'Cancelled',
                    'pending review', 'under review by sord', 'submitted to sord', 'forwarded to md' => 'Under Finalization',
                    default                 => 'Draft',
                };

                $level = match ($status) {
                    'Under Finalization' => 25,
                    default              => (int) ($creator->acc_level ?? 19),
                };

                $pghId = DB::table('prj.prghistory')->insertGetId([
                    'pgh_xprj_id'   => $doc->prj_id,
                    'pgh_dtg'       => $doc->created_at ?: now(),
                    'pgh_progress'  => $text,
                    'pgh_author'    => $creator->acc_desigshort ?? 'System',
                    'pgh_status'    => $status,
                    'pgh_closedtg'  => $status === 'Finalized' ? ($doc->updated_at ?: now()) : null,
                    'pgh_level'     => $level,
                    'pgh_group'     => $group,
                    'pgh_trail'     => '-' . (int) ($creator->acc_level ?? 19) . '-' . ($level !== (int) ($creator->acc_level ?? 19) ? $level . '-' : ''),
                    'pgh_underedit' => $status === 'Draft',
                ], 'pgh_id');

                // Its history becomes trail rows, remarks included.
                $hist = DB::table('doc.document_history')->where('doc_id', $doc->doc_id)->orderBy('hist_id')->get();
                foreach ($hist as $h) {
                    DB::table('prj.prghistory_trail')->insert([
                        'pgt_pgh_id'       => $pghId,
                        'pgt_prj_id'       => $doc->prj_id,
                        'pgt_group'        => $group,
                        'pgt_action'       => $h->action_type ?: 'Moved',
                        'pgt_from_acc_id'  => $h->from_user_id,
                        'pgt_to_acc_id'    => $h->to_user_id,
                        'pgt_from_desig'   => $h->from_name ?? null,
                        'pgt_to_desig'     => $h->to_name ?? null,
                        'pgt_status_after' => $status,
                        'pgt_remarks'      => $h->notes,
                        'pgt_dtg'          => $h->created_at ?: now(),
                    ]);
                }

                $moved++;
            }

            if ($moved > 0) {
                echo "  {$moved} document-engine MPR(s) written back into prj.prghistory." . PHP_EOL;
            }

            // ---------------------------------------------------------------
            // 4. Drop the mirrored legacy copies from doc.* (source stays intact)
            // ---------------------------------------------------------------
            $legacyIds = DB::table('doc.documents')->where('doc_is_legacy', true)->pluck('doc_id');
            if ($legacyIds->isNotEmpty()) {
                DB::table('doc.document_history')->whereIn('doc_id', $legacyIds)->delete();
                DB::table('doc.document_versions')->whereIn('doc_id', $legacyIds)->delete();
                DB::table('doc.documents')->whereIn('doc_id', $legacyIds)->delete();
                echo '  Removed ' . $legacyIds->count() . ' mirrored legacy documents from doc.*' . PHP_EOL;
            }
        }
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS prj.prghistory_trail");
    }

    /**
     * level => designation, the way the legacy DesigInStop() resolved it.
     */
    private function levelDesignations(): array
    {
        $map = [];
        foreach (DB::table('cen.roles')->orderBy('rol_unt_id')->get(['rol_level', 'rol_desigshort']) as $role) {
            $lvl = (int) $role->rol_level;
            if (! isset($map[$lvl]) && ! empty($role->rol_desigshort)) {
                $map[$lvl] = $role->rol_desigshort;
            }
        }
        return $map;
    }
};
