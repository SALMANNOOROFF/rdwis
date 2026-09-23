<?php

namespace App\Http\Controllers\Finance;

use App\Enums\RevType;
use App\Http\Controllers\Controller;
use App\Models\AudRev;
use App\Services\DataRevisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FinanceAllocationController extends Controller
{
    /**
     * Initiate a data revision for an Allocation (fin_sharesalloc).
     * Legacy fin_headstatus_rev.bas:124.
     * RevType 3 (LINKED_CASCADE) exercising verified 'Allocation - 3' cascade.
     */
    public function reverseAllocation(Request $request, $id, DataRevisionService $revisionService)
    {
        Gate::authorize('initiate', AudRev::class);

        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $alloc = DB::table('fin.sharesalloc as sa')
            ->leftJoin('cen.heads as h', 'sa.sha_hed_id', '=', 'h.hed_id')
            ->where('sa.sha_id', $id)
            ->select('sa.*', 'h.hed_name', 'h.hed_code', 'h.hed_unt_id')
            ->first();

        if (!$alloc) {
            abort(404, 'Allocation record not found.');
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isFinOrAdmin = in_array($userArea, ['fin', 'rdw', 'hqs', 'it'], true) || ($user->acc_username === 'superadminrdw');

        if (!$isFinOrAdmin) {
            $targetUnit = (int) ($alloc->hed_unt_id ?? $user->acc_unt_id ?? 0);
            if (!app(\App\Services\Auth\DataScopeService::class)->canAccessUnit($user, $targetUnit)) {
                abort(403, 'Unauthorized. Allocation is outside your unit scope.');
            }
        }

        $reason = $request->input('rev_reason') ?: $request->input('reason');
        $headName = $alloc->hed_name ?? $alloc->hed_code ?? 'Allocation';

        $revision = $revisionService->createDataRevision(
            revObject: 'Allocation',
            objectId: $alloc->sha_id,
            unitId: (int) ($alloc->hed_unt_id ?? $user->acc_unt_id),
            revType: RevType::LINKED_CASCADE, // RevType 3
            revRef: $headName,
            revObjectExt: null,
            intUnitId: (int) ($user->acc_unt_id ?? $alloc->hed_unt_id),
            revReason: $reason
        );

        return redirect()->route('admin.reversals.show', $revision->rev_id)
            ->with('success', "Data revision draft #{$revision->rev_id} for Allocation #{$id} created successfully.");
    }
}
