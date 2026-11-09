<?php

namespace App\Services;

use App\Models\GenericCase;
use App\Models\GenericCaseNote;
use App\Models\GenericCaseMovement;
use App\Models\GenericCaseAttachment;
use App\Models\CenAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenericCaseService
{
    /**
     * Generate unique official generic tracking file number.
     * Format: CF-{YEAR}-{SEQ}
     */
    public function generateFileNo(string $category = 'GEN'): string
    {
        $year = Carbon::now()->format('Y');

        $latestCase = GenericCase::whereYear('created_at', $year)
            ->orderBy('gcs_id', 'desc')
            ->first();

        $seq = 1;
        if ($latestCase && preg_match('/-(\d+)$/', $latestCase->gcs_file_no, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return sprintf("CF-%s-%04d", $year, $seq);
    }

    /**
     * Instant initiation of Draft Case (Direct open without popup, NO auto-generated dummy notes).
     */
    public function initiateDraftCase(CenAccount $creator): GenericCase
    {
        return DB::transaction(function () use ($creator) {
            $fileNo = $this->generateFileNo('General');

            $case = GenericCase::create([
                'gcs_file_no'           => $fileNo,
                'gcs_title'             => 'Untitled Case File',
                'gcs_category'          => 'General',
                'gcs_subject'           => null,
                'gcs_description'       => '1. ',
                'gcs_priority'          => 'Normal',
                'gcs_status'            => 'Draft',
                'gcs_current_stage'     => 'Division',
                'gcs_current_holder_id' => $creator->acc_id,
                'gcs_created_by'        => $creator->acc_id,
                'gcs_unt_id'            => $creator->acc_unt_id,
            ]);

            // DO NOT auto-generate dummy notes. First note will be what user writes in remarks!
            return $case;
        });
    }

    /**
     * Update draft case details (Title, Category, etc.).
     */
    public function updateDraft(GenericCase $case, CenAccount $user, array $data): GenericCase
    {
        return DB::transaction(function () use ($case, $data) {
            $updateData = [];

            if (isset($data['title'])) {
                $updateData['gcs_title'] = trim($data['title']) ?: 'Untitled Case File';
            }
            if (isset($data['category'])) {
                $updateData['gcs_category'] = trim($data['category']);
            }
            if (isset($data['subject'])) {
                $updateData['gcs_subject'] = trim($data['subject']);
            }
            if (isset($data['description'])) {
                $desc = trim($data['description']);
                if (!empty($desc) && !preg_match('/^\s*1\./', $desc)) {
                    $desc = "1. {$desc}";
                }
                $updateData['gcs_description'] = $desc;
            }

            if (!empty($updateData)) {
                $case->update($updateData);
            }

            return $case->fresh();
        });
    }

    /**
     * Send / Forward Case to a destination (Right side Action Panel).
     */
    public function sendCase(GenericCase $case, CenAccount $actor, array $payload): GenericCaseMovement
    {
        return DB::transaction(function () use ($case, $actor, $payload) {
            $destCode = $payload['target_destination'] ?? '';
            $toHolderId = null;
            $toStage = 'Under Review';

            // Extract target account ID from code (e.g. 'acc_123' or '123')
            if (preg_match('/^acc_(\d+)$/', $destCode, $m)) {
                $toHolderId = (int) $m[1];
            } elseif (is_numeric($destCode)) {
                $toHolderId = (int) $destCode;
            } elseif (!empty($payload['to_holder_id'])) {
                $toHolderId = (int) $payload['to_holder_id'];
            }

            $targetAccount = $toHolderId ? CenAccount::find($toHolderId) : null;
            if ($targetAccount) {
                $toStage = $targetAccount->acc_desigshort ?: strtoupper($targetAccount->acc_untarea ?: 'REVIEW');
            }

            $fromStage = $case->gcs_current_stage;
            $fromHolderId = $case->gcs_current_holder_id;

            // Calculate next continuous paragraph number
            $liCount = 0;
            foreach ($case->movements->where('gcm_action', '!=', 'Initiated') as $m) {
                $raw = trim($m->gcm_remarks ?? '');
                if (!empty($raw)) {
                    $lines = explode("\n", $raw);
                    $c = 0;
                    foreach ($lines as $l) {
                        if (!empty(trim($l))) $c++;
                    }
                    $liCount += max(1, $c);
                }
            }
            $nextPara = max(1, $liCount + 1);

            $remarks = trim($payload['remarks'] ?? '');
            if (!empty($remarks) && !preg_match('/^\d+[\.\)]/', $remarks)) {
                $paraContent = "{$nextPara}. {$remarks}";
            } else {
                $paraContent = $remarks ?: "{$nextPara}. Submitted for kind perusal and necessary action please.";
            }

            // Save as official paragraph in note sheet
            $case->notes()->create([
                'gcn_para_no'      => $nextPara,
                'gcn_title'        => "Minute Paragraph {$nextPara}",
                'gcn_content'      => $paraContent,
                'gcn_author_id'    => $actor->acc_id,
                'gcn_author_name'  => $actor->acc_name ?? 'Officer',
                'gcn_author_rank'  => $actor->acc_rank,
                'gcn_author_desig' => $actor->acc_desig ?? $actor->acc_desigshort,
            ]);

            // Update Case master record
            $case->update([
                'gcs_current_stage'     => $toStage,
                'gcs_current_holder_id' => $toHolderId ?: $fromHolderId,
                'gcs_status'            => 'Under Review',
            ]);

            // Save Movement / Decision
            return $case->movements()->create([
                'gcm_actor_id'       => $actor->acc_id,
                'gcm_actor_name'     => $actor->acc_name ?? 'Officer',
                'gcm_actor_rank'     => $actor->acc_rank,
                'gcm_actor_desig'    => $actor->acc_desig ?? $actor->acc_desigshort,
                'gcm_action'         => 'Forwarded',
                'gcm_from_stage'     => $fromStage,
                'gcm_to_stage'       => $toStage,
                'gcm_from_holder_id' => $fromHolderId,
                'gcm_to_holder_id'   => $toHolderId,
                'gcm_remarks'        => $paraContent,
                'gcm_dtg'            => now(),
            ]);
        });
    }

    /**
     * Approve Case (Finance / Approver action).
     */
    public function approveCase(GenericCase $case, CenAccount $actor, ?string $remarks = null): GenericCaseMovement
    {
        return DB::transaction(function () use ($case, $actor, $remarks) {
            $fromStage = $case->gcs_current_stage;
            $fromHolderId = $case->gcs_current_holder_id;

            $lastPara = (int) ($case->notes()->max('gcn_para_no') ?? 0);
            $nextPara = $lastPara + 1;
            $cleanRemarks = trim($remarks ?? '') ?: "Approved as proposed.";

            // Record note
            $case->notes()->create([
                'gcn_para_no'      => $nextPara,
                'gcn_title'        => "Approval Paragraph {$nextPara}",
                'gcn_content'      => $cleanRemarks,
                'gcn_author_id'    => $actor->acc_id,
                'gcn_author_name'  => $actor->acc_name ?? 'Approver',
                'gcn_author_rank'  => $actor->acc_rank,
                'gcn_author_desig' => $actor->acc_desig ?? $actor->acc_desigshort,
            ]);

            // Update Case status
            $case->update([
                'gcs_status'        => 'Approved',
                'gcs_current_stage' => 'Approved',
            ]);

            return $case->movements()->create([
                'gcm_actor_id'       => $actor->acc_id,
                'gcm_actor_name'     => $actor->acc_name ?? 'Approver',
                'gcm_actor_rank'     => $actor->acc_rank,
                'gcm_actor_desig'    => $actor->acc_desig ?? $actor->acc_desigshort,
                'gcm_action'         => 'Approved',
                'gcm_from_stage'     => $fromStage,
                'gcm_to_stage'       => 'Approved',
                'gcm_from_holder_id' => $fromHolderId,
                'gcm_to_holder_id'   => $fromHolderId,
                'gcm_remarks'        => $cleanRemarks,
                'gcm_dtg'            => now(),
            ]);
        });
    }

    /**
     * Reject / Cancel Case.
     */
    public function rejectCase(GenericCase $case, CenAccount $actor, ?string $remarks = null): GenericCaseMovement
    {
        return DB::transaction(function () use ($case, $actor, $remarks) {
            $fromStage = $case->gcs_current_stage;
            $fromHolderId = $case->gcs_current_holder_id;
            $cleanRemarks = trim($remarks ?? '') ?: "Case rejected / cancelled.";

            $case->update([
                'gcs_status' => 'Rejected',
            ]);

            return $case->movements()->create([
                'gcm_actor_id'       => $actor->acc_id,
                'gcm_actor_name'     => $actor->acc_name ?? 'Officer',
                'gcm_actor_rank'     => $actor->acc_rank,
                'gcm_actor_desig'    => $actor->acc_desig ?? $actor->acc_desigshort,
                'gcm_action'         => 'Rejected',
                'gcm_from_stage'     => $fromStage,
                'gcm_to_stage'       => 'Rejected',
                'gcm_from_holder_id' => $fromHolderId,
                'gcm_to_holder_id'   => null,
                'gcm_remarks'        => $cleanRemarks,
                'gcm_dtg'            => now(),
            ]);
        });
    }

    /**
     * Get available destination targets formatted for the search dropdown.
     */
    public function getAvailableDestinations(?CenAccount $currentUser = null): array
    {
        $accounts = CenAccount::whereRaw("LOWER(acc_status) = 'active'")
            ->when($currentUser, fn($q) => $q->where('acc_id', '!=', $currentUser->acc_id))
            ->orderBy('acc_name', 'asc')
            ->get();

        $list = [];
        foreach ($accounts as $acc) {
            $area = strtolower(trim((string) ($acc->acc_untarea ?? '')));
            $unitName = $acc->acc_untname ?? $acc->acc_untnamesh ?? '';
            $deptName = $unitName ?: ($acc->acc_desig ?? 'Department');

            $code = 'acc_' . $acc->acc_id;
            $list[$code] = [
                'code'     => $code,
                'stage'    => $acc->acc_desigshort ?: strtoupper($area ?: 'OFFICE'),
                'acc_id'   => $acc->acc_id,
                'name'     => $deptName . ' — ' . $acc->acc_name,
                'director' => $acc->acc_name,
                'desig'    => $acc->acc_desig,
                'badge'    => $acc->acc_desigshort ?: strtoupper($area ?: 'UNIT'),
                'area'     => $area,
            ];
        }

        return $list;
    }

    /**
     * Store an attachment for this case.
     */
    public function storeAttachment(GenericCase $case, CenAccount $uploader, UploadedFile $file, ?string $title = null): GenericCaseAttachment
    {
        $originalName = $file->getClientOriginalName();
        $cleanTitle = $title ?: pathinfo($originalName, PATHINFO_FILENAME);
        $ext = $file->getClientOriginalExtension();
        $storedName = time() . '_' . uniqid() . '.' . $ext;

        $path = $file->storeAs("cases/{$case->gcs_id}", $storedName, 'public');

        return $case->attachments()->create([
            'gca_title'       => $cleanTitle,
            'gca_filename'    => $originalName,
            'gca_path'        => $path,
            'gca_size'        => $file->getSize(),
            'gca_mime'        => $file->getClientMimeType(),
            'gca_uploader_id' => $uploader->acc_id,
            'created_at'      => now(),
        ]);
    }
}
