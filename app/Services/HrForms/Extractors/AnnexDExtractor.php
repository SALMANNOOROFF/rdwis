<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Annex D: Non-Disclosure Agreement (RDW/HR/F-03)
 *
 * Confidentiality & non-disclosure agreement between Disclosing Party (NRDI / R&D Wing)
 * and Receiving Party (selected candidate). Signed at joining.
 */
class AnnexDExtractor implements FormExtractorInterface
{
    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array
    {
        $divisionName = $case->division_name ?? 'Headquarters / Directorate';
        $projectTitle = $case->project_name ?? 'R&D Project';
        $projectCode = $case->project_code ?? null;

        // Candidate / Receiving Party Info
        $candName = $case->ctc_empnamecomp ?: null;
        $cnic = $case->candidate_cnic ?: null;
        $appl = $cnic ? DB::table('hr.applicants')->where('apl_cnic', $cnic)->first() : null;

        $live = [
            'project_title'    => $projectTitle,
            'project_code'     => $projectCode,
            'division_name'    => $divisionName,
            'designation'      => $case->ctc_newjobtitle,
            'grade'            => $case->ctc_newgrade,
            'disclosing_party' => [
                'name'  => config('hrforms.disclosing_party_name', 'NRDI / R&D Wing'),
                'cnic'  => config('hrforms.disclosing_party_cnic', null),
            ],
            'receiving_party'  => [
                'name'  => $candName,
                'cnic'  => $cnic,
            ],
        ];

        // Manual layer per FIX 5: agreement_date and signature blocks for both parties
        $manual = [
            'agreement_date' => $existingManualData['agreement_date'] ?? null,
            'disclosing_party_signature' => [
                'by'      => $existingManualData['disclosing_party_signature']['by'] ?? null,
                'name'    => $existingManualData['disclosing_party_signature']['name'] ?? null,
                'title'   => $existingManualData['disclosing_party_signature']['title'] ?? 'Authorized Representative, R&D Wing',
                'cnic'    => $existingManualData['disclosing_party_signature']['cnic'] ?? null,
                'address' => $existingManualData['disclosing_party_signature']['address'] ?? 'NRDI Headquarters, Islamabad',
                'date'    => $existingManualData['disclosing_party_signature']['date'] ?? null,
            ],
            'receiving_party_signature' => [
                'by'      => $existingManualData['receiving_party_signature']['by'] ?? null,
                'name'    => $existingManualData['receiving_party_signature']['name'] ?? $candName,
                'title'   => $existingManualData['receiving_party_signature']['title'] ?? $case->ctc_newjobtitle,
                'cnic'    => $existingManualData['receiving_party_signature']['cnic'] ?? $cnic,
                'address' => $existingManualData['receiving_party_signature']['address'] ?? ($appl->apl_paddress ?? null),
                'date'    => $existingManualData['receiving_party_signature']['date'] ?? null,
            ],
        ];

        $missingFields = [];
        if (empty($manual['agreement_date'])) {
            $missingFields[] = 'agreement_date';
        }
        if (empty($manual['disclosing_party_signature']['name'])) {
            $missingFields[] = 'disclosing_party_signature.name';
        }
        if (empty($manual['receiving_party_signature']['name'])) {
            $missingFields[] = 'receiving_party_signature.name';
        }

        return [
            'live'           => $live,
            'manual'         => $manual,
            'warnings'       => [],
            'missing_fields' => $missingFields,
            'is_complete'    => empty($missingFields),
        ];
    }
}
