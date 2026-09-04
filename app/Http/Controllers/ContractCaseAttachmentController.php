<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HrCtrCase;
use App\Models\HrCtrCaseAttachment;
use App\Services\FileStorageService;
use App\Facades\FileStorage;
use Illuminate\Support\Facades\Auth;

class ContractCaseAttachmentController extends Controller
{
    public function store($id, Request $request)
    {
        $request->validate([
            'doc_title' => 'required|string|max:255',
            'file'      => 'required|file|max:20480', // Max 20MB
        ]);

        $case = HrCtrCase::findOrFail($id);

        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No file was uploaded. Please select a valid document.'
            ], 422);
        }

        $file = $request->file('file');
        $docTitle = trim((string)$request->input('doc_title'));

        try {
            $storedPath = app(FileStorageService::class)->store(
                $file,
                'hr',
                'cat-ctc-',
                (string) $case->ctc_id
            );

            $attachment = HrCtrCaseAttachment::create([
                'cat_objtype' => 'ctc',
                'cat_objid'   => $case->ctc_id,
                'cat_type'    => $docTitle,
                'cat_path'    => $storedPath
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Attachment uploaded and linked to case successfully.',
                'attachment' => [
                    'id'       => $attachment->cat_id,
                    'title'    => $attachment->cat_type,
                    'filename' => $file->getClientOriginalName(),
                    'url'      => FileStorage::url($storedPath),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload attachment: ' . $e->getMessage()
            ], 500);
        }
    }
}
