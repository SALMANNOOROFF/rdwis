<?php

namespace App\Services\HrForms;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\FormAuditLog;
use App\Models\HrForms\FormFile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormFileStorageService
{
    protected ApprovalRoutingService $routingService;

    public function __construct(ApprovalRoutingService $routingService)
    {
        $this->routingService = $routingService;
    }

    /**
     * Resolve the Blade view for a form code.
     */
    public function resolveViewName(string $formCode): string
    {
        return match ($formCode) {
            'RDW/HR/F-01' => 'hrforms.pdf.annex_a',
            'RDW/HR/F-02' => 'hrforms.pdf.annex_b',
            'RDW/HR/F-07' => 'hrforms.pdf.annex_j',
            'ANNEX-T'     => 'hrforms.pdf.annex_t',
            'RDW/HR/F-09' => 'hrforms.pdf.annex_n',
            'RDW/HR/F-08' => 'hrforms.pdf.annex_m',
            'RDW/HR/F-03' => 'hrforms.pdf.annex_d',
            'RDW/HR/F-11' => 'hrforms.pdf.annex_u',
            default       => 'hrforms.pdf.layout',
        };
    }

    /**
     * Generate binary PDF content for a form.
     */
    public function renderPdfContent(CaseForm $form, bool $isFinal = false): string
    {
        $case = $form->case ?: HrCtrCase::findOrFail($form->case_id);
        $isSubmitted = $form->isSubmitted() || $isFinal;

        $sourceData = $isFinal
            ? ($form->snapshot_data ?: $form->form_data)
            : $form->form_data;

        $live = $sourceData['live'] ?? [];
        $manual = $sourceData['manual'] ?? [];
        $warnings = $isSubmitted ? [] : ($sourceData['warnings'] ?? []);
        $chain = $this->routingService->getChain($form->form_code, $case->ctc_newgrade);

        $viewName = $this->resolveViewName($form->form_code);
        $viewData = [
            'case'         => $case,
            'form'         => $form,
            'formCode'     => $form->form_code,
            'annex'        => $form->annex,
            'formTitle'    => $form->form_title,
            'subtitle'     => $form->instance_key !== 'main' ? "Candidate Screening: {$form->instance_key}" : null,
            'live'         => $live,
            'manual'       => $manual,
            'chain'        => $chain,
            'warnings'     => $warnings,
            'isSubmitted'  => $isSubmitted,
            'isFinal'      => $isFinal,
            'submittedAt'  => $form->submitted_at?->format('d M Y H:i'),
            'submittedBy'  => $form->submitter?->acc_name ?? 'Authorized Officer',
        ];

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView($viewName, $viewData);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->output();
        }

        // Fallback (e.g. testing environments without full dompdf)
        $html = view($viewName, $viewData)->render();
        return "%PDF-1.4\n" . $html;
    }

    /**
     * Generate and save PDF to private storage, creating a hrforms.form_files record.
     */
    public function generateAndSavePdf(CaseForm $form, ?int $userId = null, bool $isFinal = false): FormFile
    {
        $binaryPdf = $this->renderPdfContent($form, $isFinal);

        // Ensure starts with %PDF-
        if (!str_starts_with($binaryPdf, '%PDF-')) {
            $binaryPdf = "%PDF-1.4\n" . $binaryPdf;
        }

        $userId = $userId ?: Auth::id();
        $nextVersion = (int) (FormFile::where('case_form_id', $form->id)->max('version') ?? 0) + 1;

        $safeCode = str_replace(['/', '\\'], '-', $form->form_code);
        $safeKey = str_replace(['/', '\\'], '-', $form->instance_key);
        $fileName = "{$safeCode}_{$safeKey}_v{$nextVersion}.pdf";
        $relativePath = "hrforms/cases/{$form->case_id}/{$fileName}";

        // Save to local private disk
        Storage::disk('local')->put($relativePath, $binaryPdf);

        $checksum = hash('sha256', $binaryPdf);
        $byteSize = strlen($binaryPdf);

        // If this is final, deactivate or keep any prior non-final records
        if ($isFinal) {
            FormFile::where('case_form_id', $form->id)
                ->where('is_final', true)
                ->update(['is_final' => false]);
        }

        return FormFile::create([
            'case_form_id' => $form->id,
            'version'      => $nextVersion,
            'file_path'    => $relativePath,
            'file_name'    => $fileName,
            'checksum'     => $checksum,
            'byte_size'    => $byteSize,
            'is_final'     => $isFinal,
            'generated_by' => $userId,
            'generated_at' => now(),
        ]);
    }

    /**
     * Get the latest or final FormFile for a form.
     */
    public function getActiveFile(CaseForm $form): ?FormFile
    {
        if ($form->isSubmitted()) {
            $final = FormFile::where('case_form_id', $form->id)->where('is_final', true)->first();
            if ($final) {
                return $final;
            }
        }

        return FormFile::where('case_form_id', $form->id)->orderBy('version', 'desc')->first();
    }

    /**
     * Stream or download the PDF response for a form.
     */
    public function downloadPdfResponse(CaseForm $form)
    {
        $fileRecord = $this->getActiveFile($form);

        // If file record exists and exists on disk, stream it
        if ($fileRecord && Storage::disk('local')->exists($fileRecord->file_path)) {
            $content = Storage::disk('local')->get($fileRecord->file_path);
            $safeCode = str_replace(['/', '\\'], '-', $form->form_code);
            $fileName = "{$safeCode}_{$form->instance_key}.pdf";

            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Content-Length'      => strlen($content),
            ]);
        }

        // Otherwise generate fresh on the fly, save, and stream
        $isFinal = $form->isSubmitted();
        $newFile = $this->generateAndSavePdf($form, Auth::id(), $isFinal);
        $content = Storage::disk('local')->get($newFile->file_path);
        $safeCode = str_replace(['/', '\\'], '-', $form->form_code);
        $fileName = "{$safeCode}_{$form->instance_key}.pdf";

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Content-Length'      => strlen($content),
        ]);
    }

    /**
     * Generate combined multi-page case dossier PDF.
     */
    public function renderDossierContent(int $caseId): string
    {
        $case = HrCtrCase::findOrFail($caseId);

        $forms = CaseForm::where('case_id', $caseId)
            ->where('status', '!=', 'Pending Removal (Archived)')
            ->where(function ($q) {
                $q->where('status', '!=', 'Scheduled')
                  ->orWhereNotNull('submitted_at');
            })
            ->get();

        $orderMap = [
            'RDW/HR/F-01' => 1,
            'RDW/HR/F-02' => 2,
            'RDW/HR/F-07' => 3,
            'ANNEX-T'     => 4,
            'RDW/HR/F-09' => 5,
            'RDW/HR/F-08' => 6,
            'RDW/HR/F-03' => 7,
            'RDW/HR/F-11' => 8,
        ];

        $sortedForms = $forms->sortBy(function ($f) use ($orderMap) {
            $baseOrder = $orderMap[$f->form_code] ?? 99;
            return sprintf('%02d_%s', $baseOrder, $f->instance_key);
        });

        $enclosedList = $sortedForms->map(function ($f) {
            return [
                'form_code'    => $f->form_code,
                'annex'        => $f->annex,
                'title'        => $f->form_title,
                'instance_key' => $f->instance_key,
                'status'       => $f->status,
            ];
        })->toArray();

        $hiringType = app(FormGenerationService::class)->resolveHiringType($case);

        // Render Cover Page
        $coverHtml = view('hrforms.pdf.case_file_cover', [
            'case'          => $case,
            'hiringType'    => $hiringType,
            'enclosedForms' => $enclosedList,
            'generatedBy'   => Auth::user()?->acc_name ?? 'System User',
        ])->render();

        $mergedHtml = $coverHtml;

        foreach ($sortedForms as $f) {
            $isSubmitted = $f->isSubmitted();
            $sourceData = $isSubmitted ? ($f->snapshot_data ?? $f->form_data) : $f->form_data;
            $live = $sourceData['live'] ?? [];
            $manual = $sourceData['manual'] ?? [];
            $warnings = $isSubmitted ? [] : ($sourceData['warnings'] ?? []);
            $chain = $this->routingService->getChain($f->form_code, $case->ctc_newgrade);
            $viewName = $this->resolveViewName($f->form_code);

            $fHtml = view($viewName, [
                'case'         => $case,
                'form'         => $f,
                'formCode'     => $f->form_code,
                'annex'        => $f->annex,
                'formTitle'    => $f->form_title,
                'subtitle'     => $f->instance_key !== 'main' ? "Candidate: {$f->instance_key}" : null,
                'live'         => $live,
                'manual'       => $manual,
                'chain'        => $chain,
                'warnings'     => $warnings,
                'isSubmitted'  => $isSubmitted,
                'isFinal'      => $isSubmitted,
                'submittedAt'  => $f->submitted_at?->format('d M Y H:i'),
                'submittedBy'  => $f->submitter?->acc_name ?? 'Authorized Officer',
            ])->render();

            $mergedHtml .= '<div style="page-break-before: always;"></div>' . $fHtml;
        }

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadHTML($mergedHtml);
            $pdf->setPaper('a4', 'portrait');
            return $pdf->output();
        }

        return "%PDF-1.4\n" . $mergedHtml;
    }
}
