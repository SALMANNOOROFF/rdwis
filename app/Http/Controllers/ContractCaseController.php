<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractCaseController extends Controller
{
    public function projectPanel(Request $request, int $id, int $headId)
    {
        $case = \App\Models\HrCtrCase::with('casePlans')->findOrFail($id);
        $this->authorize('view', $case);
        $allocation = app(\App\Services\ContractCaseProjectService::class)
            ->allocations($case)->firstWhere('hed_id', $headId);
        abort_unless($allocation, 404, 'This project is not allocated to this contract case.');

        $section = $request->query('section', 'financial');
        abort_unless(in_array($section, ['financial', 'attachments', 'milestones'], true), 422);
        $financial = null;
        $attachments = collect();
        $milestones = collect();
        if ($section === 'financial') {
            $financial = app(\App\Services\FinancialIntelligenceService::class)->getHeadStatus($headId);
        } elseif ($allocation->hed_prj_id && $section === 'attachments') {
            $attachments = DB::table('prj.prjattachments')->where('jat_objid', $allocation->hed_prj_id)
                ->whereIn('jat_objtype', ['prj', 'Project'])->whereNotNull('jat_path')
                ->where('jat_path', '<>', '')->get();
        } elseif ($allocation->hed_prj_id && $section === 'milestones') {
            $milestones = DB::table('prj.milestones')->where('msn_xprj_id', $allocation->hed_prj_id)
                ->orderBy('msn_id')->get();
        }

        return response()->json([
            'allocation' => $allocation,
            'section' => $section,
            'financial' => $financial,
            'attachments' => $attachments,
            'milestones' => $milestones,
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * Render printable official A4 case sheet for any authorized viewer (Division, HR, Fin, IT, DLOG, MD, DDG, DG).
     */
    public function printCase(int $id)
    {
        $case = \App\Models\HrCtrCase::with([
            'employee',
            'previousContract',
            'newContract',
            'casePlans.project',
            'attachments',
            'remarksHistory',
            'unit',
            'division',
            'newUnit',
            'currentSubstatus',
        ])->findOrFail($id);

        $this->authorize('view', $case);

        // Chronological seat-wise remarks trail (includes HR fulfillment remark once fulfilled)
        $remarksList = $case->remarksHistory
            ->sortBy(fn($r) => [$r->crr_dtg ? strtotime((string)$r->crr_dtg) : 0, $r->crr_id])
            ->values()
            ->map(function ($r) {
                $statusRaw = strtoupper(trim((string) $r->crr_status));
                $tone = 'normal';
                if (str_contains($statusRaw, 'APPROV') || str_contains($statusRaw, 'FULFILL')) {
                    $tone = 'approve';
                } elseif (str_contains($statusRaw, 'RETURN') || str_contains($statusRaw, 'REVIS')) {
                    $tone = 'return';
                } elseif (str_contains($statusRaw, 'REJECT') || str_contains($statusRaw, 'NOT APPROVED') || str_contains($statusRaw, 'CANCEL')) {
                    $tone = 'reject';
                }

                $seat = trim((string) ($r->crr_user_desig ?: ($r->crr_user_rank ?: 'Officer')));

                return [
                    'actor'     => $r->crr_username ?: 'Authorized Officer',
                    'seat'      => strtoupper($seat),
                    'action'    => $r->crr_status ?: 'Processed',
                    'tone'      => $tone,
                    'timestamp' => $r->crr_dtg ? \Carbon\Carbon::parse($r->crr_dtg)->format('d M Y, h:i A') : '—',
                    'remarks'   => $r->crr_remarks ?? '',
                ];
            })
            ->all();

        // Consolidated attached files (Case Attachments + HR Forms if generated)
        $enclosuresList = [];
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        foreach ($case->attachments->filter(fn($a) => !empty(trim((string)$a->cat_path))) as $att) {
            $normPath = str_replace('\\', '/', (string)$att->cat_path);
            $fn = basename($normPath);
            $ext = strtolower(pathinfo($normPath, PATHINFO_EXTENSION));
            $label = trim((string)($att->cat_type ?: '')) ?: $fn;
            $uploadedBy = null;
            if (!empty($att->cat_acc_id)) {
                $uploadedBy = DB::table('cen.accounts')->where('acc_id', $att->cat_acc_id)->value('acc_name');
            }

            $enclosuresList[] = [
                'title'       => $label,
                'filename'    => $fn,
                'source'      => 'Contract Case Attachment',
                'uploaded_by' => $uploadedBy ?: ($case->division_name ?: 'Division / HR'),
                'meta'        => strtoupper($ext ?: 'FILE'),
                'url'         => route('universal.attachment.view', ['module' => 'ctc', 'id' => $att->cat_id]),
                'is_image'    => in_array($ext, $imageExts, true),
                'is_pdf'      => $ext === 'pdf',
            ];
        }

        // Check HR Forms (if hrforms schema/table exists and forms are generated for this case)
        $hrForms = collect();
        try {
            $hrForms = DB::table('hrforms.case_forms')
                ->where('case_id', $case->ctc_id)
                ->where('status', '!=', 'Pending Removal (Archived)')
                ->orderBy('id')
                ->get();

            $hrFormsEnabled = config('hrforms.enabled', false);
            foreach ($hrForms as $hf) {
                $subAt = !empty($hf->submitted_at) ? \Carbon\Carbon::parse($hf->submitted_at)->format('d M Y, h:i A') : null;
                $subBy = !empty($hf->submitted_by) ? DB::table('cen.accounts')->where('acc_id', $hf->submitted_by)->value('acc_name') : null;
                $enclosuresList[] = [
                    'title'       => ($hf->form_code ? $hf->form_code . ' — ' : '') . ($hf->form_title ?: 'HR Form'),
                    'filename'    => ($hf->annex ?: 'Form') . ($hf->instance_key && $hf->instance_key !== 'main' ? " ({$hf->instance_key})" : ''),
                    'source'      => 'HR Case File Form',
                    'uploaded_by' => $subBy,
                    'meta'        => ($hf->status ?: 'Draft') . ($subAt ? " ({$subAt})" : ''),
                    'url'         => $hrFormsEnabled ? route('hrforms.forms.pdf', $hf->id) : null,
                    'is_image'    => false,
                    'is_pdf'      => $hrFormsEnabled,
                ];
            }
        } catch (\Throwable $e) {
            // Silently ignore if hrforms table does not exist
        }

        return view('md.contract-cases.print', compact('case', 'remarksList', 'enclosuresList', 'hrForms'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isDlog = (method_exists($user, 'isDlog') && $user->isDlog()) || in_array($area, ['log', 'dlog', 'logistics'], true);
        $context = \App\Services\Auth\UserAccessContext::forUser($user);
        $isDdg = $context->isDdg() || $area === 'hqs';

        // Route to the specialized dashboards built for Contract Cases
        if ($isDlog) {
            return redirect()->route('dlog.contract-cases.index');
        } elseif ($isDdg) {
            return redirect()->route('ddg.contract-cases.index');
        } elseif ($area === 'hr') {
            return redirect()->route('hr.contract-cases.index');
        } elseif ($area === 'fin') {
            return redirect()->route('finance.contract-cases.index');
        } elseif ($area === 'rdw') {
            return redirect()->route('md.contract-cases.index');
        } elseif ($area === 'nrdi') {
            return redirect()->route('dg.contract-cases.index');
        } elseif ($area === 'it') {
            return redirect()->route('it.contract-cases.index');
        } else {
            // Default to Division Initiator dashboard
            return redirect()->route('division.contract-cases.index');
        }
    }
}
