<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Annex U: Personal Data Form (RDW/HR/F-11)
 *
 * Implements the 10 PDF sections:
 * 1. Personal Information
 * 2. Next of Kin
 * 3. Emergency Contact
 * 4. Education
 * 5. Professional Courses / Certifications
 * 6. Professional Experience
 * 7. Vehicles
 * 8. Bank Account Details (Meezan Bank)
 * 9. Research Publications / Dissertation
 * 10. References
 */
class AnnexUExtractor implements FormExtractorInterface
{
    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array
    {
        $divisionName = $case->division_name ?? 'Headquarters / Directorate';
        $projectTitle = $case->project_name ?? 'R&D Project';

        // 1. Resolve employee and applicant records
        $emp = $case->employee;
        $empId = $emp->emp_id ?? null;
        $candName = $case->ctc_empnamecomp ?: ($emp->emp_name ?? null);
        $cnic = $case->candidate_cnic ?: ($emp->emp_cnic ?? null);

        $applicant = $cnic ? DB::table('hr.applicants')->where('apl_cnic', $cnic)->first() : null;
        $empA = $empId ? DB::table('hr.empsexta')->where('empexta_emp_id', $empId)->first() : null;
        $empB = $empId ? DB::table('hr.empsextb')->where('empextb_emp_id', $empId)->first() : null;

        // Fetch education
        $education = [];
        if ($empId) {
            $qualRows = DB::table('hr.qualifs')->where('qlf_emp_id', $empId)->get();
            foreach ($qualRows as $q) {
                $education[] = [
                    'degree_name' => $q->qlf_name ?? null,
                    'institute'   => $q->qlf_inst ?? null,
                    'year'        => $q->qlf_enddt ? Carbon::parse($q->qlf_enddt)->format('Y') : null,
                    'grade_gpa'   => $q->qlf_grade ?? null,
                ];
            }
        } elseif ($applicant) {
            $qualRows = DB::table('hr.applicqualifs')->where('apq_apl_id', $applicant->apl_id)->get();
            foreach ($qualRows as $q) {
                $education[] = [
                    'degree_name' => $q->apq_name ?? null,
                    'institute'   => $q->apq_inst ?? null,
                    'year'        => $q->apq_enddt ? Carbon::parse($q->apq_enddt)->format('Y') : null,
                    'grade_gpa'   => $q->apq_grade ?? null,
                ];
            }
        }

        // Fetch experience
        $experience = [];
        if ($empId) {
            $jobRows = DB::table('hr.jobs')->where('job_emp_id', $empId)->get();
            foreach ($jobRows as $j) {
                $experience[] = [
                    'organization' => $j->job_company ?? null,
                    'designation'  => $j->job_title ?? null,
                    'from'         => $j->job_from ? Carbon::parse($j->job_from)->format('Y-m-d') : null,
                    'to'           => $j->job_to ? Carbon::parse($j->job_to)->format('Y-m-d') : null,
                ];
            }
        } elseif ($applicant) {
            $jobRows = DB::table('hr.applicjobs')->where('apj_apl_id', $applicant->apl_id)->get();
            foreach ($jobRows as $j) {
                $experience[] = [
                    'organization' => $j->apj_company ?? null,
                    'designation'  => $j->apj_jobtitle ?? null,
                    'from'         => $j->apj_from ? Carbon::parse($j->apj_from)->format('Y-m-d') : null,
                    'to'           => $j->apj_to ? Carbon::parse($j->apj_to)->format('Y-m-d') : null,
                ];
            }
        }

        // Fetch vehicles
        $vehicles = [];
        if ($empId) {
            $vclRows = DB::table('hr.vehicles')->where('vcl_emp_id', $empId)->get();
            foreach ($vclRows as $v) {
                $vehicles[] = [
                    'type'     => $v->vcl_type ?? null,
                    'maker'    => $v->vcl_maker ?? null,
                    'variant'  => $v->vcl_variant ?? null,
                    'reg_no'   => $v->vcl_regis ?? null,
                    'year'     => $v->vcl_year ?? null,
                    'color'    => $v->vcl_color ?? null,
                ];
            }
        }

        // Fetch bank accounts (Meezan Bank)
        $bankDetails = [];
        if ($empId) {
            $bnkRows = DB::table('hr.bnkaccounts')->where('bac_emp_id', $empId)->get();
            foreach ($bnkRows as $b) {
                $bankDetails[] = [
                    'bank_name'     => $b->bac_bnkname ?? 'Meezan Bank',
                    'branch_name'   => $b->bac_bchname ?? null,
                    'branch_code'   => $b->bac_bchcode ?? null,
                    'account_title' => $b->bac_acctitle ?? null,
                    'account_number'=> $b->bac_accnum ?? null,
                    'city'          => $b->bac_bchcity ?? null,
                ];
            }
        }

        // 1. Personal Information (Live + manual fallback)
        $dob = $empA->emp_dob ?? ($applicant->apl_dob ?? null);
        $dobFmt = $dob ? Carbon::parse($dob)->format('Y-m-d') : null;

        $livePersonalInfo = [
            'full_name'            => $candName,
            'cnic'                 => $cnic,
            'highest_qualification'=> $education[0]['degree_name'] ?? ($applicant->apl_discip ?? null),
            'discipline'           => $empA->emp_discip ?? ($applicant->apl_discip ?? null),
            'speciality'           => $empA->emp_spec ?? ($applicant->apl_spec ?? null),
            'fathers_name'         => $empA->emp_father ?? ($applicant->apl_father ?? null),
            'gender'               => $empA->emp_gender ?? ($applicant->apl_gender ?? null),
            'date_of_birth'        => $dobFmt,
            'place_of_birth'       => $empA->emp_pob ?? ($applicant->apl_pob ?? null),
            'nationality'          => $empA->emp_ntnlty ?? ($applicant->apl_ntnlty ?? 'Pakistani'),
            'marital_status'       => $empA->emp_marital ?? ($applicant->apl_marital ?? null),
            'email'                => $empA->emp_email ?? ($applicant->apl_email ?? null),
            'mobile_1'             => $empA->emp_mobile ?? ($applicant->apl_mobile ?? null),
            'mobile_2'             => $empA->emp_mobile2 ?? ($applicant->apl_mobile2 ?? null),
            'landline'             => $empA->emp_landline ?? ($applicant->apl_landline ?? null),
            'current_address'      => $empA->emp_taddress ?? ($applicant->apl_taddress ?? null),
            'permanent_address'    => $empA->emp_paddress ?? ($applicant->apl_paddress ?? null),
            'id_mark'              => $empB->emp_idmark ?? null,
            'height_cm'            => $empB->emp_height ?? null,
            'cast'                 => $empB->emp_caste ?? null,
            'religion'             => $empB->emp_religion ?? 'Islam',
            'sect'                 => $empB->emp_sect ?? null,
            'police_station'       => $empB->emp_police ?? null,
            'political_affiliation'=> $empB->emp_political ?? 'None',
        ];

        // 2. Next of Kin
        $liveNextOfKin = [
            'name'       => $empB->emp_nokname ?? null,
            'relation'   => $empB->emp_nokrelation ?? null,
            'cnic'       => $empB->emp_nokcnic ?? null,
            'contact_no' => null,
        ];

        // 3. Emergency Contact
        $liveEmergencyContact = [
            'name'       => $empB->emp_emername ?? null,
            'relation'   => $empB->emp_emerrelation ?? null,
            'contact_no' => $empB->emp_emermobile ?? null,
        ];

        $live = [
            'designation'   => $case->ctc_newjobtitle,
            'grade'         => $case->ctc_newgrade,
            'division_name' => $divisionName,
            'project_name'  => $projectTitle,
        ];

        // 10 sections manual layer with live defaults
        $manual = [
            'section_1_personal_info'           => array_merge($livePersonalInfo, $existingManualData['section_1_personal_info'] ?? []),
            'section_2_next_of_kin'             => array_merge($liveNextOfKin, $existingManualData['section_2_next_of_kin'] ?? []),
            'section_3_emergency_contact'       => array_merge($liveEmergencyContact, $existingManualData['section_3_emergency_contact'] ?? []),
            'section_4_education'               => !empty($existingManualData['section_4_education']) ? $existingManualData['section_4_education'] : $education,
            'section_5_professional_courses'    => $existingManualData['section_5_professional_courses'] ?? [],
            'section_6_professional_experience' => !empty($existingManualData['section_6_professional_experience']) ? $existingManualData['section_6_professional_experience'] : $experience,
            'section_7_vehicles'                => !empty($existingManualData['section_7_vehicles']) ? $existingManualData['section_7_vehicles'] : $vehicles,
            'section_8_bank_account'            => !empty($existingManualData['section_8_bank_account']) ? $existingManualData['section_8_bank_account'] : $bankDetails,
            'section_9_publications'            => $existingManualData['section_9_publications'] ?? [],
            'section_10_references'             => $existingManualData['section_10_references'] ?? [],
        ];

        $missingFields = [];
        if (empty($manual['section_1_personal_info']['date_of_birth'])) {
            $missingFields[] = 'section_1_personal_info.date_of_birth';
        }
        if (empty($manual['section_2_next_of_kin']['name'])) {
            $missingFields[] = 'section_2_next_of_kin.name';
        }
        if (empty($manual['section_3_emergency_contact']['name'])) {
            $missingFields[] = 'section_3_emergency_contact.name';
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
