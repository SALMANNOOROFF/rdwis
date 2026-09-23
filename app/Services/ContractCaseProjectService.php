<?php

namespace App\Services;

use App\Models\HrCtrCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContractCaseProjectService
{
    public function allocations(HrCtrCase $case): Collection
    {
        $headIds = $case->casePlans->sortBy('ccp_startdt')->pluck('ccp_hed_id')
            ->filter()->unique()->values();
        if ($headIds->isEmpty() && $case->ctc_prj_id) {
            $headIds->push($case->ctc_prj_id);
        }

        return DB::table('cen.heads as h')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->whereIn('h.hed_id', $headIds)
            ->select('h.hed_id', 'h.hed_prj_id', 'h.hed_code', 'h.hed_name', 'p.prj_code', 'p.prj_title as prj_name')
            ->get()->sortBy(fn ($head) => $headIds->search($head->hed_id))->values();
    }

    /** Count people, not contracts; current dated plans take precedence over the employee's base head. */
    public function hiredCounts(Collection $allocations): Collection
    {
        if ($allocations->isEmpty()) {
            return collect();
        }

        $today = today()->toDateString();
        $currentPlans = DB::table('hr.contractplans as cp')
            ->join('hr.contracts as c', 'c.ctr_id', '=', 'cp.cpn_ctr_id')
            ->whereDate('cp.cpn_startdt', '<=', $today)
            ->whereDate('cp.cpn_enddt', '>=', $today)
            ->whereNotNull('cp.cpn_hed_id')
            ->where(function ($query) use ($today) {
                $query->whereNull('c.ctr_termindt')->orWhereDate('c.ctr_termindt', '>=', $today);
            })
            ->orderByDesc('cp.cpn_startdt')->orderByDesc('c.ctr_id')
            ->get(['c.ctr_num', 'cp.cpn_hed_id'])->unique('ctr_num')->keyBy('ctr_num');

        $heads = DB::table('cen.heads')->get(['hed_id', 'hed_prj_id'])->keyBy('hed_id');
        $people = DB::table('hr.emps')->whereRaw("LOWER(TRIM(emp_status)) IN ('active', 'current')")
            ->get(['emp_id', 'emp_hed_id']);
        $counts = [];
        foreach ($people as $person) {
            $headId = $currentPlans->get($person->emp_id)?->cpn_hed_id ?? $person->emp_hed_id;
            $projectId = $heads->get($headId)?->hed_prj_id;
            $key = $projectId ? 'project:'.$projectId : 'head:'.$headId;
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $allocations->mapWithKeys(fn ($head) => [
            $head->hed_id => $counts[$head->hed_prj_id ? 'project:'.$head->hed_prj_id : 'head:'.$head->hed_id] ?? 0,
        ]);
    }
}
