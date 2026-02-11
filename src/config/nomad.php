<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch to enable or disable timezone conversion globally.
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Timezone Table
    |--------------------------------------------------------------------------
    |
    | The database table that stores the user's timezone. Typically 'users'.
    |
    */

    'table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Timezone Column
    |--------------------------------------------------------------------------
    |
    | The column name in the table above that stores the timezone string.
    |
    */

    'column' => 'timezone',

    /*
    |--------------------------------------------------------------------------
    | Auth Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard used to resolve the current user.
    | Set to null to use the application's default guard.
    |
    */

    'guard' => null,

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Models listed here will NOT have their datetime attributes converted
    | to the user's timezone. Use fully qualified class names.
    |
    */

    'excluded_models' => [
        // \App\Models\AuditLog::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Output Timezone
    |--------------------------------------------------------------------------
    |
    | A fallback timezone when no user-specific timezone is available.
    | If null, falls back to the application timezone (typically UTC).
    |
    */

    'default_output_timezone' => env('NOMAD_OUTPUT_TIMEZONE'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Header Name
    |--------------------------------------------------------------------------
    |
    | The HTTP header the frontend should send with the user's IANA timezone.
    | The frontend can detect this with: Intl.DateTimeFormat().resolvedOptions().timeZone
    |
    */

    'header' => 'X-Timezone',

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to persist the detected timezone across requests.
    |
    */

    'session_key' => 'nomad_timezone',

    /*
    |--------------------------------------------------------------------------
    | GeoIP Configuration
    |--------------------------------------------------------------------------
    |
    | Enable GeoIP lookup as a timezone detection fallback.
    | Requires the torann/geoip package: composer require torann/geoip
    |
    */

    'geoip' => [
        'enabled' => false,
    ],
];
