<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HR Forms Auto-Generation Feature Flag
    |--------------------------------------------------------------------------
    |
    | When set to false (default), the auto-generation feature is inactive,
    | ensuring zero impact on standard contract case workflow.
    |
    */
    'enabled' => env('HRFORMS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Single Candidate Mode for Rehiring
    |--------------------------------------------------------------------------
    |
    | Admin-configurable flag. If true, rehiring cases do not require 3 candidates.
    | A mandatory justification field is required on the case.
    |
    */
    'rehiring_single_candidate' => env('HRFORMS_REHIRING_SINGLE_CANDIDATE', true),

    /*
    |--------------------------------------------------------------------------
    | Intern Extension Board Forms Exclusion (Para 31g)
    |--------------------------------------------------------------------------
    |
    | Para 31(g): Intern extension needs MD RDW approval only.
    | When true, extension or renewal of internship generates no board forms.
    |
    */
    'intern_extension_no_board_forms' => env('HRFORMS_INTERN_EXTENSION_NO_BOARD_FORMS', true),

    /*
    |--------------------------------------------------------------------------
    | Intern Keywords (Configurable Detection)
    |--------------------------------------------------------------------------
    */
    'intern_keywords' => [
        'grades'     => ['INTERNEE', 'INTERN'],
        'job_titles' => ['intern', 'internee', 'trainee'],
    ],

    /*
    |--------------------------------------------------------------------------
    | RT and Below Fallback Grades
    |--------------------------------------------------------------------------
    */
    'rt_and_below_grades' => [
        'SRT', 'RT', 'JRT',
        'LA', 'LAB ATTENDANT',
        'RA', 'RESEARCH AIDE',
        'EA', 'ENGINEERING AIDE',
        'SS', 'SUPPORT STAFF',
        'LABOR', 'WORKER', 'GARDENER', 'NAIB QASID', 'DIVER', 'MAALI',
        'JA', 'JUNIOR ASSISTANT',
        'INTERN', 'INTERNEE',
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Name
    |--------------------------------------------------------------------------
    */
    'schema' => 'hrforms',
];
