<?php

namespace App\Http\Controllers;

use App\Facades\FileStorage;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
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

        if ($existing) {
            DB::table($cfg['table'])
                ->where($cfg['pk'], $existing->{$cfg['pk']})
                ->update([$cfg['path'] => $storedPath]);
            $slotId = $existing->{$cfg['pk']};
        } else {
            $slotId = DB::table($cfg['table'])->insertGetId([
                $cfg['objtype'] => $cfg['default_objtype'],
                $cfg['objid'] => $objectId,
                $cfg['type'] => $docType,
                $cfg['path'] => $storedPath,
            ], $cfg['pk']);
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

        $download = $request->query('download') === '1';
        $filename = $record->{$cfg['type']} ? ($record->{$cfg['type']} . '.' . pathinfo($record->{$cfg['path']}, PATHINFO_EXTENSION)) : null;

        return $this->storage->response($record->{$cfg['path']}, $filename, $download);
    }

    /**
     * Delete an attachment.
     */
    public function delete(Request $request, string $module, $id)
    {
        $cfg = $this->getModuleConfig($module);

        $record = DB::table($cfg['table'])
            ->where($cfg['pk'], $id)
            ->first();

        if ($record) {
            if (!empty($record->{$cfg['path']})) {
                $this->storage->delete($record->{$cfg['path']});
            }

            DB::table($cfg['table'])
                ->where($cfg['pk'], $id)
                ->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }
}
