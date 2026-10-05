<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use Illuminate\Support\Facades\DB;

/**
 * Annex T: Comparison Matrix of Shortlisted Candidates
 *
 * Extracts shortlisted candidate comparative table, qualifications, and relaxation justifications.
 */
class AnnexTExtractor implements FormExtractorInterface
{
    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array
    {
        $caseExtra = CaseExtra::where('case_id', $case->ctc_id)->first();
        $isSingleCandidate = $caseExtra->single_candidate_mode ?? true;

        // Principal candidate info
        $c1Name = $case->ctc_empnamecomp;
        $c1Cnic = $case->candidate_cnic;
        $appl = $c1Cnic ? DB::table('hr.applicants')->where('apl_cnic', $c1Cnic)->first() : null;
        $c1Qual = $appl ? ($appl->apl_discip . ' (' . $appl->apl_spec . ')') : 'N/A';
        $c1Exp = $appl ? (string) $appl->apl_experience : 'N/A';

        $liveCandidates = [
            [
                's_no'               => 1,
                'name'               => $c1Name,
                'cnic'               => $c1Cnic,
                'qualification'      => $c1Qual,
                'experience_years'   => $c1Exp,
                'skills'             => $appl->apl_spec ?? 'Core domain skills',
                'remarks'            => 'Principal Candidate recommended by Division',
            ]
        ];

        // If extra candidates exist in case_extras
        $shortlist = $caseExtra->shortlisted_candidates ?? [];
        if (!empty($shortlist) && is_array($shortlist)) {
            $sNo = 2;
            foreach ($shortlist as $cand) {
                if ($sNo > 3) break;
                $liveCandidates[] = [
                    's_no'             => $sNo,
                    'name'             => $cand['name'] ?? "Candidate {$sNo}",
                    'cnic'             => $cand['cnic'] ?? '',
                    'qualification'    => $cand['qualification'] ?? 'Relevant Degree',
                    'experience_years' => $cand['experience'] ?? '1-2 Years',
                    'skills'           => $cand['skills'] ?? '',
                    'remarks'          => $cand['remarks'] ?? 'Shortlisted candidate',
                ];
                $sNo++;
            }
        }

        $live = [
            'position_applied'    => $case->ctc_newjobtitle,
            'grade'               => $case->ctc_newgrade,
            'division_name'       => $case->division_name ?? 'Headquarters',
            'project_name'        => $case->project_name ?? 'R&D Project',
            'candidates_count'    => count($liveCandidates),
            'single_candidate'    => $isSingleCandidate,
        ];

        $manual = [
            'candidates'                    => $existingManualData['candidates'] ?? $liveCandidates,
            'justification_relaxation'      => $existingManualData['justification_relaxation'] ?? ($caseExtra->single_candidate_justification ?? null),
            'director_signature_remarks'    => $existingManualData['director_signature_remarks'] ?? 'Recommended for Selection Board interview',
        ];

        $missingFields = [];
        if ($isSingleCandidate && empty($manual['justification_relaxation'])) {
            $missingFields[] = 'justification_relaxation (single-candidate / relaxed candidate justification)';
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
