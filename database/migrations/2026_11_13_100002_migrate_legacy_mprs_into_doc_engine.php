<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Move every legacy MPR (prj.prghistory) into the doc.* engine so the new MPR
 * screens, history and reports show the full record, not just what was raised
 * after the 2.0 switch-over.
 *
 *   one (project + monthly group)  ->  one doc.documents row
 *   every prghistory row           ->  one doc.document_versions row (0.1, 0.2 ...)
 *   every movement                 ->  one doc.document_history row
 *
 * Legacy actors are designations (DSensors, DR&D ...), not accounts, so the ids
 * stay NULL and the name is carried in the *_name columns.
 */
return new class extends Migration
{
    /** legacy status  =>  [document status, stage] */
    private const STATUS_MAP = [
        'draft'              => ['Draft',          'ProjectOfficer'],
        'edited'             => ['Draft',          'ProjectOfficer'],
        'under review'       => ['Returned',       'ProjectOfficer'],
        'under finalization' => ['Pending Review', 'SORD'],
        'finalized'          => ['Finalized',      'Accepted'],
        'cancelled'          => ['Cancelled',      'ProjectOfficer'],
        'submitted'          => ['Pending Review', 'SORD'],
    ];

    public function up(): void
    {
        if (DB::table('doc.documents')->where('doc_is_legacy', true)->exists()) {
            echo "  Legacy MPRs already migrated - nothing to do." . PHP_EOL;
            return;
        }

        $rows = DB::table('prj.prghistory as h')
            ->join('prj.projects as p', 'p.prj_id', '=', 'h.pgh_xprj_id')
            ->orderBy('h.pgh_xprj_id')
            ->orderBy('h.pgh_group')
            ->orderBy('h.pgh_dtg')
            ->orderBy('h.pgh_id')
            ->select([
                'h.pgh_id', 'h.pgh_xprj_id', 'h.pgh_dtg', 'h.pgh_progress', 'h.pgh_author',
                'h.pgh_status', 'h.pgh_closedtg', 'h.pgh_level', 'h.pgh_group', 'h.pgh_trail',
                'h.pgh_underedit', 'h.pgh_path',
                'p.prj_code', 'p.prj_unt_id',
            ])
            ->get();

        if ($rows->isEmpty()) {
            echo "  No legacy MPR rows found." . PHP_EOL;
            return;
        }

        $buckets = [];
        foreach ($rows as $row) {
            $key = $row->pgh_xprj_id . '|' . trim((string) $row->pgh_group);
            $buckets[$key][] = $row;
        }

        $docCount = 0;
        $verCount = 0;
        $hisCount = 0;
        $seqUsed = [];   // prj|period => highest seq

        DB::transaction(function () use ($buckets, &$docCount, &$verCount, &$hisCount, &$seqUsed) {
            foreach ($buckets as $bucket) {
                $first = $bucket[0];
                $last  = $bucket[count($bucket) - 1];

                [$period, $label] = $this->periodOf($first);

                $seqKey = $first->pgh_xprj_id . '|' . $period;
                $seqUsed[$seqKey] = ($seqUsed[$seqKey] ?? 0) + 1;
                $seq = $seqUsed[$seqKey];

                [$status, $stage] = $this->mapStatus($last->pgh_status, $last->pgh_level);

                $finalizedAt = strcasecmp(trim((string) $last->pgh_status), 'Finalized') === 0
                    ? ($last->pgh_closedtg ?: $last->pgh_dtg)
                    : null;

                $docId = DB::table('doc.documents')->insertGetId([
                    'prj_id'           => $first->pgh_xprj_id,
                    'doc_type'         => 'MPR',
                    'doc_ref_no'       => 'MPR/' . ($first->prj_code ?: $first->pgh_xprj_id) . '/' . $period . '/' . $seq,
                    'current_owner_id' => null,
                    'creator_id'       => null,
                    'status'           => $status,
                    'created_at'       => $first->pgh_dtg,
                    'updated_at'       => $last->pgh_closedtg ?: $last->pgh_dtg,
                    'doc_period'       => $period,
                    'doc_period_label' => $label,
                    'doc_seq'          => $seq,
                    'doc_stage'        => $stage,
                    'doc_unt_id'       => $first->prj_unt_id,
                    'doc_is_legacy'    => true,
                    'doc_legacy_group' => $first->pgh_group,
                    'doc_creator_name' => $first->pgh_author,
                    'doc_owner_name'   => $last->pgh_author,
                    'doc_finalized_at' => $finalizedAt,
                ], 'doc_id');
                $docCount++;

                $i = 0;
                $prevAuthor = null;
                foreach ($bucket as $row) {
                    $i++;
                    $action = $this->actionOf($row->pgh_status, $i === 1);
                    [$rowStatus, $rowStage] = $this->mapStatus($row->pgh_status, $row->pgh_level);

                    DB::table('doc.document_versions')->insert([
                        'doc_id'          => $docId,
                        'version_no'      => number_format($i * 0.1, 1),
                        'content_data'    => json_encode([
                            'progress' => $row->pgh_progress,
                            'legacy'   => [
                                'pgh_id'     => $row->pgh_id,
                                'level'      => $row->pgh_level,
                                'trail'      => $row->pgh_trail,
                                'status'     => $row->pgh_status,
                                'group'      => $row->pgh_group,
                                'path'       => $row->pgh_path,
                                'underedit'  => $row->pgh_underedit,
                                'closed_dtg' => $row->pgh_closedtg,
                            ],
                        ]),
                        'remarks'         => null,
                        'action_by'       => null,
                        'action_date'     => $row->pgh_dtg,
                        'ver_stage'       => $rowStage,
                        'ver_action'      => $action,
                        'ver_status'      => $row->pgh_status,
                        'ver_author_name' => $row->pgh_author,
                        'ver_is_legacy'   => true,
                    ]);
                    $verCount++;

                    DB::table('doc.document_history')->insert([
                        'doc_id'       => $docId,
                        'from_user_id' => null,
                        'to_user_id'   => null,
                        'action_type'  => $action,
                        'notes'        => 'Legacy MPR (' . trim((string) $row->pgh_group) . ') - ' . $row->pgh_status,
                        'created_at'   => $row->pgh_dtg,
                        'from_name'    => $prevAuthor ?: $row->pgh_author,
                        'to_name'      => $row->pgh_author,
                        'stage'        => $rowStage,
                    ]);
                    $hisCount++;

                    $prevAuthor = $row->pgh_author;
                }
            }
        });

        echo "  Legacy MPRs migrated: {$docCount} documents, {$verCount} versions, {$hisCount} history entries." . PHP_EOL;
    }

    public function down(): void
    {
        $ids = DB::table('doc.documents')->where('doc_is_legacy', true)->pluck('doc_id');
        if ($ids->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($ids) {
            DB::table('doc.document_history')->whereIn('doc_id', $ids)->delete();
            DB::table('doc.document_versions')->whereIn('doc_id', $ids)->delete();
            DB::table('doc.documents')->whereIn('doc_id', $ids)->delete();
        });
    }

    /**
     * 'Sep 24' => ['2024-09', 'Sep 24']; falls back to the entry date.
     */
    private function periodOf($row): array
    {
        $group = trim((string) ($row->pgh_group ?? ''));

        if ($group !== '') {
            try {
                $d = Carbon::createFromFormat('M y', $group);
                if ($d) {
                    return [$d->format('Y-m'), $group];
                }
            } catch (\Throwable $e) {
                // fall through
            }
        }

        $d = $row->pgh_dtg ? Carbon::parse($row->pgh_dtg) : Carbon::now();
        return [$d->format('Y-m'), $group !== '' ? $group : $d->format('M y')];
    }

    private function mapStatus($legacyStatus, $level): array
    {
        $key = strtolower(trim((string) $legacyStatus));
        $mapped = self::STATUS_MAP[$key] ?? ['Draft', 'ProjectOfficer'];

        // The level refines the stage: legacy route was -19-(25)-, anything above 25 is command.
        $lvl = (int) $level;
        if ($lvl > 0 && $mapped[0] !== 'Finalized' && $mapped[0] !== 'Cancelled') {
            if ($lvl >= 30) {
                $mapped[1] = 'MD';
            } elseif ($lvl >= 25) {
                $mapped[1] = 'SORD';
            } else {
                $mapped[1] = 'ProjectOfficer';
            }
        }

        return $mapped;
    }

    private function actionOf($legacyStatus, bool $isFirst): string
    {
        $key = strtolower(trim((string) $legacyStatus));

        return match ($key) {
            'edited'             => 'Edited',
            'under finalization' => 'Forwarded',
            'under review'       => 'Returned',
            'finalized'          => 'Finalized',
            'cancelled'          => 'Cancelled',
            default              => $isFirst ? 'Created' : 'Updated',
        };
    }
};
