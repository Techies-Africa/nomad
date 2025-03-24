<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Timezone Table Name
    |--------------------------------------------------------------------------
    |
    | This value is the table to save the timezone of your users. If nothing
    | is entered, It takes the users table as the default.
    |
    */

    'table' => 'users',
    'guard' => '',

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | This value takes in the models you want to be excluded from formatting. If
    | a model entered, the model maintains it original timestamp.
    |
    */

    'excluded_models' => [
        // App\Models\User,
        // App\Models\Admin,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Application Timezone
    |--------------------------------------------------------------------------
    |
    | This takes in the default timezone for each user. If a timezone is
    | entered, the timezone overrides the original timezone saved in the
    | selected table. If nothing is entered it takes the default timezone
    | in the app.php
    |
    */

    'default_output_timezone' => env("NOMAD_OUTPUT_TIMEZONE"),
];
