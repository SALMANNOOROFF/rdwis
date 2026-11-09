<?php

namespace App\Http\Controllers;

use App\Facades\FileStorage;
use App\Services\FileStorageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentController extends Controller
{
    protected FileStorageService $storage;

    public function __construct(FileStorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Map modules to table metadata.
     */
    protected function getModuleConfig(string $module): array
    {
        $map = [
            'prj' => [
                'table' => 'prj.prjattachments',
                'pk' => 'jat_id',
                'objtype' => 'jat_objtype',
                'objid' => 'jat_objid',
                'type' => 'jat_type',
                'path' => 'jat_path',
                'default_objtype' => 'prj',
                'subfolder' => 'prj',
                'prefix_map' => [
                    'project proposal' => 'ppr-prj-',
                    'ppr' => 'ppr-prj-',
                    'urd' => 'urd-prj-',
                    'work order' => 'wo-prj-',
                    'wo' => 'wo-prj-',
                    'ppf' => 'ppf-prj-',
                    'approval letter' => 'apl-prj-',
                    'minute' => 'min-prj-',
                    'default' => 'mx-prj-',
                ],
            ],
            'pur' => [
                'table' => 'pur.purattachments',
                'pk' => 'pat_id',
                'objtype' => 'pat_objtype',
                'objid' => 'pat_objid',
                'type' => 'pat_type',
                'path' => 'pat_path',
                'default_objtype' => 'pcs',
                'subfolder' => 'pur',
                'prefix_map' => [
                    'purchase case' => 'pcs-',
                    'quotation document' => 'pcs-',
                    'market research report' => 'mrr-',
                    'financial status' => 'fs-',
                    'minute' => 'min-',
                    'default' => 'pcs-',
                ],
            ],
            'emp' => [
                'table' => 'hr.empattachments',
                'pk' => 'eat_id',
                'objtype' => 'eat_objtype',
                'objid' => 'eat_objid',
                'type' => 'eat_type',
                'path' => 'eat_path',
                'default_objtype' => 'emp',
                'subfolder' => 'hr',
                'prefix_map' => [
                    'appointment letter' => 'apl-emp-',
                    'form' => 'frm-emp-',
                    'minute' => 'min-emp-',
                    'cv' => 'mx-emp-',
                    'default' => 'eat-emp-',
                ],
            ],
            'ctc' => [
                'table' => 'hr.ctrcaseattachments',
                'pk' => 'cat_id',
                'objtype' => 'cat_objtype',
                'objid' => 'cat_objid',
                'type' => 'cat_type',
                'path' => 'cat_path',
                'default_objtype' => 'ctc',
                'subfolder' => 'hr',
                'prefix_map' => [
                    'cv' => 'mx-ctc-',
                    'approval' => 'mx-ctc-',
                    'minute' => 'min-ctc-',
                    'form' => 'frm-ctc-',
                    'default' => 'ctc-',
                ],
            ],
            'aud' => [
                'table' => 'aud.audattachments',
                'pk' => 'aat_id',
                'objtype' => 'aat_objtype',
                'objid' => 'aat_objid',
                'type' => 'aat_type',
                'path' => 'aat_path',
                'default_objtype' => 'rev',
                'subfolder' => 'aud',
                'prefix_map' => [
                    'data revision case' => 'rev-',
                    'minute' => 'min-',
                    'default' => 'rev-',
                ],
            ],
            'ina' => [
                'table' => 'ina.inaattachments',
                'pk' => 'iat_id',
                'objtype' => 'iat_objtype',
                'objid' => 'iat_objid',
                'type' => 'iat_type',
                'path' => 'iat_path',
                'default_objtype' => 'ina',
                'subfolder' => 'ina',
                'prefix_map' => [
                    'default' => 'mx-ina-',
                ],
            ],
        ];

        $key = strtolower(trim($module));
        if (!isset($map[$key])) {
            throw new \InvalidArgumentException("Invalid attachment module: {$module}");
        }

        return $map[$key];
    }

    /**
     * Upload an attachment for a specific slot or custom document.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:prj,pur,emp,ctc,aud,ina',
            'object_id' => 'required',
            'doc_type' => 'required|string|max:100',
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        $module = $request->input('module');
        $objectId = $request->input('object_id');
        $docType = trim($request->input('doc_type'));
        $file = $request->file('file');

        if ($module === 'aud') {
            $rev = \App\Models\AudRev::find($objectId);
            if (! $rev || (! $rev->isFulfilled() && ! $rev->isCancelled()) || ! auth()->user()?->can('viewAttachments', $rev)) {
                abort(403, 'Unauthorized. Attachments are restricted to closed reversals and authorized roles.');
            }
        }

        $cfg = $this->getModuleConfig($module);

        // Determine prefix
        $typeLower = strtolower($docType);
        $prefix = $cfg['prefix_map'][$typeLower] ?? ($cfg['prefix_map']['default'] ?? 'mx-');

        // Check if a slot already exists with an old file and delete the old physical file
        $existing = DB::table($cfg['table'])
            ->where($cfg['objid'], $objectId)
            ->where($cfg['type'], $docType)
            ->first();

        if ($existing && !empty($existing->{$cfg['path']})) {
            $this->storage->delete($existing->{$cfg['path']});
        }

        // Store new file
        $storedPath = $this->storage->store($file, $cfg['subfolder'], $prefix, (string) $objectId);
        $user = Auth::user();
        $accId = $user ? $user->acc_id : null;
        $untId = $user ? $user->acc_unt_id : null;

        if ($existing) {
            $updateData = [$cfg['path'] => $storedPath];
            if ($module === 'pur') {
                $updateData['pat_acc_id'] = $accId;
                $updateData['pat_unt_id'] = $untId;
                $updateData['pat_dt'] = Carbon::now();
            } elseif ($module === 'ctc') {
                $updateData['cat_acc_id'] = $accId;
                $updateData['cat_unt_id'] = $untId;
                $updateData['cat_dt'] = Carbon::now();
            }
            DB::table($cfg['table'])
                ->where($cfg['pk'], $existing->{$cfg['pk']})
                ->update($updateData);
            $slotId = $existing->{$cfg['pk']};
        } else {
            $insertData = [
                $cfg['objtype'] => $cfg['default_objtype'],
                $cfg['objid'] => $objectId,
                $cfg['type'] => $docType,
                $cfg['path'] => $storedPath,
            ];
            if ($module === 'pur') {
                $insertData['pat_acc_id'] = $accId;
                $insertData['pat_unt_id'] = $untId;
                $insertData['pat_dt'] = Carbon::now();
            } elseif ($module === 'ctc') {
                $insertData['cat_acc_id'] = $accId;
                $insertData['cat_unt_id'] = $untId;
                $insertData['cat_dt'] = Carbon::now();
            } elseif ($module === 'ina') {
                $insertData['iat_acc_id'] = $accId;
                $insertData['iat_unt_id'] = $untId;
                $insertData['iat_dt'] = Carbon::now();
            }
            $slotId = DB::table($cfg['table'])->insertGetId($insertData, $cfg['pk']);
        }

        // Legacy secondary write for ctc approval
        if ($module === 'ctc' && strcasecmp($docType, 'Approval') === 0) {
            DB::table('hr.contracts')
                ->where('ctr_ctc_id', $objectId)
                ->update(['ctr_path2' => $storedPath]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "'{$docType}' uploaded successfully.",
                'slot_id' => $slotId,
                'path' => $storedPath,
                'url' => FileStorage::url($storedPath),
            ]);
        }

        return redirect()->back()->with('success', "'{$docType}' uploaded successfully.");
    }

    /**
     * Check if a user can delete or replace a specific attachment based on case stage and ownership.
     */
    public static function canManageAttachment($user, string $module, $objectId, $attachment = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) {
            return false;
        }

        // 1. SuperAdmin & System Admins can always manage attachments
        if ($user->acc_username === 'superadminrdw'
            || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || (method_exists($user, 'isCommand') && $user->isCommand())
            || in_array(strtolower(trim((string)($user->acc_untarea ?? ''))), ['admin', 'adm'], true)
        ) {
            return true;
        }

        $userArea = strtolower(trim((string)($user->acc_untarea ?? '')));
        $module = strtolower(trim($module));

        // 2. PURCHASE CASES ('pur')
        if ($module === 'pur') {
            // Finance and Procurement roles can edit/delete any attachment on purchase cases
            if (in_array($userArea, ['fin', 'finance', 'proc', 'rdw', 'nrdi', 'hqs', 'it'], true)
                || (method_exists($user, 'isFinance') && $user->isFinance())
                || (method_exists($user, 'isProcurement') && $user->isProcurement())
            ) {
                return true;
            }

            // Division / Regular Users:
            $case = DB::table('pur.purcases')->where('pcs_id', $objectId)->first();
            if (!$case) {
                return false;
            }

            // Disallow if case is finalized
            $status = strtolower(trim((string)($case->pcs_status ?? '')));
            if (in_array($status, ['approved', 'rejected', 'cancelled', 'fulfilled', 'completed'])) {
                return false;
            }

            // Check current stage from pur.purcase_substatus
            $substatus = DB::table('pur.purcase_substatus')
                ->where('pss_pcs_id', $objectId)
                ->where('pss_is_current', true)
                ->first();
            $stage = $substatus->pss_stage ?? ($case->pcs_status === 'Draft' ? 'Division' : 'Division');

            // Sender Lock: If division has forwarded the case, locked until recipient acts or returns it
            $latestDecision = DB::table('pur.purdecisions')
                ->where('pdec_pcs_id', $objectId)
                ->orderByDesc('pdec_id')
                ->first();

            if ($latestDecision 
                && (int)$latestDecision->pdec_acc_id === (int)$user->acc_id 
                && in_array(strtolower($latestDecision->pdec_action ?? ''), ['forward', 'forward_negative', 'float_to_proc', 'reshare_to_proc'])
            ) {
                return false;
            }

            if ($stage !== 'Division' && !in_array($status, ['draft', 'returned', 'action required'])) {
                return false;
            }

            // Must be the uploader of this specific attachment (or same division)
            if ($attachment) {
                $attAccId = data_get($attachment, 'pat_acc_id');
                $attUntId = data_get($attachment, 'pat_unt_id');
                if ($attAccId) {
                    return ((int)$attAccId === (int)$user->acc_id) || ((int)$attUntId === (int)$user->acc_unt_id);
                }
            }

            // Fallback for legacy attachments without pat_acc_id: check case ownership
            return ((int)($case->pcs_acc_id ?? 0) === (int)$user->acc_id) || ((int)($case->pcs_unt_id ?? 0) === (int)$user->acc_unt_id);
        }

        // 3. HIRING / CONTRACT CASES ('ctc')
        if ($module === 'ctc') {
            // HR and Finance can edit/delete any attachment on hiring cases
            if (in_array($userArea, ['hr', 'hqs', 'fin', 'finance', 'rdw', 'nrdi', 'it'], true)
                || (method_exists($user, 'isHr') && $user->isHr())
                || (method_exists($user, 'isFinance') && $user->isFinance())
            ) {
                return true;
            }

            // Division / Originator:
            $case = DB::table('hr.ctrcases')->where('ctc_id', $objectId)->first();
            if (!$case) {
                return false;
            }

            // Disallow if case is finalized
            $status = strtolower(trim((string)($case->ctc_status ?? '')));
            if (in_array($status, ['approved', 'rejected', 'cancelled', 'fulfilled', 'completed'])) {
                return false;
            }

            // Check current stage from hr.ctrcase_substatus
            $substatus = DB::table('hr.ctrcase_substatus')
                ->where('css_ctc_id', $objectId)
                ->where('css_is_current', true)
                ->first();
            $stage = $substatus->css_stage ?? ($case->current_stage ?? 'Division');

            // If not at Division stage and not Draft/Returned/Revision, locked!
            if ($stage !== 'Division' && !in_array($status, ['draft', 'returned', 'action required', 'under revision'])) {
                return false;
            }

            // Must be the uploader of this specific attachment (or same division)
            if ($attachment) {
                $attAccId = data_get($attachment, 'cat_acc_id');
                $attUntId = data_get($attachment, 'cat_unt_id');
                if ($attAccId) {
                    return ((int)$attAccId === (int)$user->acc_id) || ((int)$attUntId === (int)$user->acc_unt_id);
                }
            }

            // Fallback for legacy attachments: check case creator/division
            return ((int)($case->ctc_acc_id ?? 0) === (int)$user->acc_id) 
                || ((int)($case->ctc_unt_id ?? 0) === (int)$user->acc_unt_id)
                || ((int)($case->ctc_divisionid ?? 0) === (int)$user->acc_unt_id);
        }

        // 4. FUND SHIFTING CASES ('ina')
        if ($module === 'ina') {
            // Finance and Command/IT can edit/delete any attachment on fund shifting cases
            if (in_array($userArea, ['fin', 'finance', 'it', 'is', 'rdw', 'nrdi', 'hqs', 'admin', 'adm'], true)
                || (method_exists($user, 'isFinance') && $user->isFinance())
            ) {
                return true;
            }

            $shift = DB::table('fsh.fund_shifts')->where('fsh_id', $objectId)->first();
            if (!$shift) {
                return false;
            }

            $status = strtolower(trim((string)($shift->fsh_status ?? '')));
            if (in_array($status, ['implemented', 'cancelled', 'rejected', 'approved'])) {
                return false;
            }

            // Sender Lock: If user forwarded/sent the case, locked until returned
            $latestDecision = DB::table('fsh.fund_shift_decisions')
                ->where('fsd_fsh_id', $objectId)
                ->orderByDesc('fsd_id')
                ->first();

            if ($latestDecision 
                && (int)$latestDecision->fsd_acc_id === (int)$user->acc_id 
                && in_array(strtolower($latestDecision->fsd_action ?? ''), ['forward', 'submit', 'approve'])
            ) {
                return false;
            }

            if ($attachment) {
                $attAccId = data_get($attachment, 'iat_acc_id');
                $attUntId = data_get($attachment, 'iat_unt_id');
                if ($attAccId) {
                    return ((int)$attAccId === (int)$user->acc_id) || ((int)$attUntId === (int)$user->acc_unt_id);
                }
            }

            return ((int)($shift->fsh_created_by ?? 0) === (int)$user->acc_id) || ((int)($shift->fsh_unt_id ?? 0) === (int)$user->acc_unt_id);
        }

        // Default allowed for other modules
        return true;
    }

    /**
     * View or download an attachment.
     */
    public function view(Request $request, string $module, $id): BinaryFileResponse
    {
        $cfg = $this->getModuleConfig($module);

        $record = DB::table($cfg['table'])
            ->where($cfg['pk'], $id)
            ->first();

        if (!$record || empty($record->{$cfg['path']})) {
            abort(404, 'Attachment record or file path not found.');
        }

        if ($module === 'aud') {
            $rev = \App\Models\AudRev::find($record->aat_objid);
            if (! $rev || (! $rev->isFulfilled() && ! $rev->isCancelled()) || ! auth()->user()?->can('viewAttachments', $rev)) {
                abort(403, 'Unauthorized. Attachment viewing is restricted to closed reversals and authorized roles.');
            }
        }

        $download = $request->query('download') === '1';
        $filename = $record->{$cfg['type']} ? ($record->{$cfg['type']} . '.' . pathinfo($record->{$cfg['path']}, PATHINFO_EXTENSION)) : null;

        return $this->storage->response($record->{$cfg['path']}, $filename, $download);
    }

    /**
     * Delete an attachment with stage & ownership guard.
     */
    public function delete(Request $request, string $module, $id)
    {
        $cfg = $this->getModuleConfig($module);
        $record = DB::table($cfg['table'])->where($cfg['pk'], $id)->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);
        }

        $objectId = $record->{$cfg['objid']};
        $user = Auth::user();

        if (!self::canManageAttachment($user, $module, $objectId, $record)) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied: This case has either been sent forward or you do not have permission to delete this attachment.',
            ], 403);
        }

        if (!empty($record->{$cfg['path']})) {
            $this->storage->delete($record->{$cfg['path']});
        }

        DB::table($cfg['table'])->where($cfg['pk'], $id)->delete();

        // Legacy cleanup for ctc approval
        if ($module === 'ctc' && strcasecmp($record->{$cfg['type']} ?? '', 'Approval') === 0) {
            DB::table('hr.contracts')
                ->where('ctr_ctc_id', $objectId)
                ->update(['ctr_path2' => null]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }

    /**
     * Replace an existing attachment with a new physical file.
     */
    public function replace(Request $request, string $module, $id)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
            'doc_type' => 'nullable|string|max:100',
        ]);

        $cfg = $this->getModuleConfig($module);
        $record = DB::table($cfg['table'])->where($cfg['pk'], $id)->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);
        }

        $objectId = $record->{$cfg['objid']};
        $user = Auth::user();

        if (!self::canManageAttachment($user, $module, $objectId, $record)) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied: This case has either been sent forward or you do not have permission to replace this attachment.',
            ], 403);
        }

        $file = $request->file('file');
        $docType = trim($request->input('doc_type') ?: ($record->{$cfg['type']} ?? 'Document'));
        $typeLower = strtolower($docType);
        $prefix = $cfg['prefix_map'][$typeLower] ?? ($cfg['prefix_map']['default'] ?? 'mx-');

        // Delete old physical file
        if (!empty($record->{$cfg['path']})) {
            $this->storage->delete($record->{$cfg['path']});
        }

        // Store new file
        $storedPath = $this->storage->store($file, $cfg['subfolder'], $prefix, (string) $objectId);

        $updateData = [
            $cfg['path'] => $storedPath,
            $cfg['type'] => $docType,
        ];
        if ($module === 'pur') {
            $updateData['pat_acc_id'] = $user->acc_id;
            $updateData['pat_unt_id'] = $user->acc_unt_id;
            $updateData['pat_dt'] = Carbon::now();
        } elseif ($module === 'ctc') {
            $updateData['cat_acc_id'] = $user->acc_id;
            $updateData['cat_unt_id'] = $user->acc_unt_id;
            $updateData['cat_dt'] = Carbon::now();
        } elseif ($module === 'ina') {
            $updateData['iat_acc_id'] = $user->acc_id;
            $updateData['iat_unt_id'] = $user->acc_unt_id;
            $updateData['iat_dt'] = Carbon::now();
        }

        DB::table($cfg['table'])->where($cfg['pk'], $id)->update($updateData);

        // Legacy sync for ctc approval
        if ($module === 'ctc' && strcasecmp($docType, 'Approval') === 0) {
            DB::table('hr.contracts')
                ->where('ctr_ctc_id', $objectId)
                ->update(['ctr_path2' => $storedPath]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "'{$docType}' replaced successfully.",
                'slot_id' => $id,
                'path' => $storedPath,
                'url' => FileStorage::url($storedPath),
            ]);
        }

        return redirect()->back()->with('success', "'{$docType}' replaced successfully.");
    }

    /**
     * Direct storage file serving route (/storage/{path})
     * Handles previews, streaming, and cross-network requests.
     */
    public function serveStorageFile(Request $request, string $path): BinaryFileResponse
    {
        $fullPath = $this->storage->resolvePhysicalPath($path);

        if (!$fullPath) {
            abort(404, 'Requested document not found on storage disk.');
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'txt'  => 'text/plain',
            'csv'  => 'text/csv',
        ];
        $mime = $mimeTypes[$ext] ?? (mime_content_type($fullPath) ?: 'application/octet-stream');
        $download = $request->query('download') === '1';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . basename($fullPath) . '"',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
