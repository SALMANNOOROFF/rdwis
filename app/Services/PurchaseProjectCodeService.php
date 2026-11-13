<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Resolves, in bulk, the project behind a set of purchase cases.
 *
 * pur.purcases carries the financial head id (pcs_hed_id, or pcs_effhed_id for
 * CSRF cases); in this schema a project head's hed_id equals prj.projects.prj_id
 * and cen.heads.hed_prj_id points at the same project.
 *
 * Returns [pcs_id => ['code' => 'MWDC', 'title' => 'Mine Warfare Data Center']]
 * using only two queries, so it is safe for long case lists.
 */
class PurchaseProjectCodeService
{
    /**
     * @param  iterable|null  ...$collections  Purchase model collections
     */
    public static function mapFor(...$collections): array
    {
        $cases = [];

        foreach ($collections as $collection) {
            if (empty($collection)) {
                continue;
            }
            foreach ($collection as $case) {
                $pcsId = (int) ($case->pcs_id ?? 0);
                if ($pcsId <= 0) {
                    continue;
                }
                $cases[$pcsId] = [
                    'hid'  => (int) ($case->pcs_hed_id ?: $case->pcs_effhed_id ?: 0),
                    'sudo' => trim((string) ($case->pcs_sudohed ?? '')),
                ];
            }
        }

        if (empty($cases)) {
            return [];
        }

        $headIds = array_values(array_unique(array_filter(array_column($cases, 'hid'))));

        $heads = empty($headIds)
            ? collect()
            : DB::table('cen.heads')
                ->whereIn('hed_id', $headIds)
                ->get(['hed_id', 'hed_code', 'hed_name', 'hed_prj_id'])
                ->keyBy('hed_id');

        $projectIds = $headIds;
        foreach ($heads as $head) {
            if (!empty($head->hed_prj_id)) {
                $projectIds[] = (int) $head->hed_prj_id;
            }
        }
        $projectIds = array_values(array_unique(array_filter($projectIds)));

        $projects = empty($projectIds)
            ? collect()
            : DB::table('prj.projects')
                ->whereIn('prj_id', $projectIds)
                ->get(['prj_id', 'prj_code', 'prj_title'])
                ->keyBy('prj_id');

        $out = [];

        foreach ($cases as $pcsId => $info) {
            $head = $heads->get($info['hid']);
            $prjId = $head && !empty($head->hed_prj_id) ? (int) $head->hed_prj_id : $info['hid'];
            $project = $projects->get($prjId);

            $code = null;
            $title = null;

            if ($project) {
                $code = self::clean($project->prj_code) ?: self::clean($project->prj_title);
                $title = self::clean($project->prj_title);
            }

            if (!$code && $head) {
                $code = self::clean($head->hed_code) ?: self::clean($head->hed_name);
                $title = $title ?: self::clean($head->hed_name);
            }

            if (!$code && $info['sudo'] !== '') {
                $code = $info['sudo'];
            }

            $out[$pcsId] = [
                'code'  => $code,
                'title' => $title ?: $code,
            ];
        }

        return $out;
    }

    /**
     * Legacy placeholders ('xxx', '000') are treated as empty.
     */
    protected static function clean($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || strcasecmp($value, 'xxx') === 0 || $value === '000') {
            return null;
        }
        return $value;
    }
}
