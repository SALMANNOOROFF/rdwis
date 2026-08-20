<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed IP Addresses Configuration
    |--------------------------------------------------------------------------
    |
    | Add your allowed IP addresses, pools, or ranges here.
    | This file is secure and cannot be accessed via URL.
    |
    | Examples:
    | '192.168.1.100',               // Individual IP
    | '10.120.29.1-10.120.29.200',   // IP Range
    | '10.120.29.5-20',              // Shorthand IP Range
    | '10.120.29.0/24',              // CIDR notation
    |
    */

    'allowed' => [
        '127.0.0.1',
        '::1',
        '192.168.0.0/16',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.1',
        '192.168.1.160',
        '192.168.1.155-165',
        '10.120.29.1-10.120.29.200',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocked IP Addresses Configuration
    |--------------------------------------------------------------------------
    |
    | Any IP listed here will be blocked, even if it falls within an allowed range.
    | Useful for blocking specific IPs from a larger allowed pool.
    |
    */

    'blocked' => [
        // Add specific IPs to block below:
        // '10.120.29.5',
     
    ],
];
