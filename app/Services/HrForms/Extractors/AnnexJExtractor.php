<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;

/**
 * Annex J: Initial Interview / Screening Form (RDW/HR/F-07)
 *
 * Extracts candidate details and 12-point evaluation criteria (1-5 scale = 60 total).
 * Divided into: A Educational Background, B Personality Traits, C Technical Competency.
 */
class AnnexJExtractor implements FormExtractorInterface
{
    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array
    {
        $divisionName = $case->division_name ?? 'Headquarters / Directorate';
        $projectTitle = $case->project_name ?? 'R&D Project';
        $position = $case->ctc_newjobtitle;

        // Resolve candidate for this instance
        $caseExtra = CaseExtra::where('case_id', $case->ctc_id)->first();
        $candidateName = $case->ctc_empnamecomp ?: null;
        $candidateCnic = $case->candidate_cnic ?: null;

        if ($instanceKey === 'cand_2' || $instanceKey === 'cand_3') {
            $shortlist = $caseExtra->shortlisted_candidates ?? [];
            $idx = ($instanceKey === 'cand_2') ? 1 : 2;
            if (isset($shortlist[$idx])) {
                $candidateName = $shortlist[$idx]['name'] ?? null;
                $candidateCnic = $shortlist[$idx]['cnic'] ?? null;
            } else {
                $candidateName = null;
                $candidateCnic = null;
            }
        }

        $criteriaDefs = [
            ['key' => 'academic_qualification_grades_gpa', 'label' => 'Academic Qualification (Grades / GPA)', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'additional_certifications_diploma', 'label' => 'Additional Certifications / Diploma', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'presentation_communication_skills', 'label' => 'Presentation & Communication Skills', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'ability_to_listen_express_effectively', 'label' => 'Ability to Listen & Express Effectively', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'personnel_attire', 'label' => 'Personnel Attire', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'attitude_professionalism', 'label' => 'Attitude & Professionalism', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'self_confidence_energy_passion', 'label' => 'Self Confidence, Energy & Passion', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'technical_skills', 'label' => 'Technical Skills', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'previous_experience', 'label' => 'Previous Experience', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'skills_competency_level', 'label' => 'Skills & Competency Level', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'accomplishments_achievements', 'label' => 'Accomplishments / Achievements', 'min_score' => 1, 'max_score' => 5],
            ['key' => 'project_similar_size_or_scope', 'label' => 'Projects of Similar Size / Scope', 'min_score' => 1, 'max_score' => 5],
        ];

        // Live layer: Candidate name and CNIC live layer only per FIX 2
        $live = [
            'evaluation_criteria' => $criteriaDefs,
            'maximum_score'       => 60,
            'candidate_name'      => $candidateName,
            'candidate_cnic'      => $candidateCnic,
            'position_applied'    => $position,
            'grade'               => $case->ctc_newgrade,
            'division_name'       => $divisionName,
            'project_name'        => $projectTitle,
            'instance_key'        => $instanceKey,
        ];

        // 12 Scoring criteria (1 to 5 scale, total 60)
        // A Educational Background (2)
        // B Personality Traits (5)
        // C Technical Competency (5)
        $criteriaKeys = [
            // A Educational Background
            'academic_qualification_grades_gpa',
            'additional_certifications_diploma',
            // B Personality Traits
            'presentation_communication_skills',
            'ability_to_listen_express_effectively',
            'personnel_attire',
            'attitude_professionalism',
            'self_confidence_energy_passion',
            // C Technical Competency
            'technical_skills',
            'previous_experience',
            'skills_competency_level',
            'accomplishments_achievements',
            'project_similar_size_or_scope',
        ];

        $scores = [];
        $totalScore = 0;
        $allScored = true;

        foreach ($criteriaKeys as $k) {
            $val = $existingManualData['scores'][$k] ?? null;
            if ($val !== null && is_numeric($val)) {
                $score = max(1, min(5, (int) $val));
                $scores[$k] = $score;
                $totalScore += $score;
            } else {
                $scores[$k] = null;
                $allScored = false;
            }
        }

        $manual = [
            'interview_date_time'             => $existingManualData['interview_date_time'] ?? ($caseExtra?->interview_date ? $caseExtra->interview_date->format('Y-m-d') . ' ' . ($caseExtra->interview_time ?? '10:00') : null),
            'scores'                          => $scores,
            'total_score'                     => $allScored ? $totalScore : null,
            'remarks_by_concerned_dir_rep'    => $existingManualData['remarks_by_concerned_dir_rep'] ?? null,
            'remarks_by_dir_hr_so_hr'         => $existingManualData['remarks_by_dir_hr_so_hr'] ?? null,
            'recommendation_status'           => $existingManualData['recommendation_status'] ?? null,
        ];

        $missingFields = [];
        if (!$allScored) {
            $missingFields[] = 'scores';
        }
        if (empty($manual['remarks_by_concerned_dir_rep'])) {
            $missingFields[] = 'remarks_by_concerned_dir_rep';
        }
        if (empty($manual['remarks_by_dir_hr_so_hr'])) {
            $missingFields[] = 'remarks_by_dir_hr_so_hr';
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
