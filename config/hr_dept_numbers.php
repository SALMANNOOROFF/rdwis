<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Department Number Prefix Map for Employee ID Generation
    |--------------------------------------------------------------------------
    |
    | Confirmed mapping from live production database (267 historical records):
    | - unt_id 200000 (Comm)    => 11
    | - unt_id 250000 (Enab)    => 12
    | - unt_id 300000 (NWS)     => 13
    | - unt_id 350000 (Sensors) => 14
    | - unt_id 400000 (SoSE)    => 15
    | - unt_id 450000 (Sys)     => 16
    | - unt_id 800000 (Finance) => 17
    | - unt_id 840000 (Admin)   => 18
    | - unt_id 820000 (HR)      => 19
    | - unt_id 880000 (IS)      => 21
    |
    | Unconfirmed departments (no historical employee records in dump):
    | - unt_id 860000 (IT)          => UNKNOWN
    | - unt_id 810000 (Procurement) => UNKNOWN
    |
    */

    'map' => [
        200000 => '11', // Communication Division
        250000 => '12', // Enabling Technology Division
        300000 => '13', // Naval Weapons System Division
        350000 => '14', // Sensors Division
        400000 => '15', // System of Systems Engineering Division
        450000 => '16', // Systems Division
        800000 => '17', // Finance Department
        840000 => '18', // Administration Department
        820000 => '19', // Human Resource Department
        880000 => '21', // Information System Department
    ],

    // Known departments without historical prefix
    'unconfirmed' => [
        860000 => 'Information Technology Department',
        810000 => 'Procurement Department',
    ],
];
