<?php

/*
|--------------------------------------------------------------------------
| Ypa Abstract API
|--------------------------------------------------------------------------
|
| The following options should be equal in all applications.
|
*/

return [


    /*
    |--------------------------------------------------------------------------
    | Time Differences
    |--------------------------------------------------------------------------
    |
    | Each Request & Response will have a timestamp. This value is in seconds
    | and sets the difference in time between the two timestamps that is valid.
    | It should be the same on all ends!
    |
    */

    'timedifferences' => env('YPA_ABSTRACT_API_TIME_DIFFERENCES', 30),

    /*
    |--------------------------------------------------------------------------
    | Hash Salt Secret
    |--------------------------------------------------------------------------
    |
    | Each Request & Response will have an encoded value. This value sets the
    | type of encoding. It should be the same on all ends!
    |
    */
    'hashsecret' => env('YPA_ABSTRACT_API_HASH_SECRET', env('APP_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Hashtype
    |--------------------------------------------------------------------------
    |
    | Each Request & Response will have an encoded value. This value sets the
    | type of encoding. It should be the same on all ends!
    |
    */
    'hashtype' => env('YPA_ABSTRACT_API_HASHTYPE', 'sha512'),

    /*
    |--------------------------------------------------------------------------
    | Debug mode
    |--------------------------------------------------------------------------
    |
    | Using this security can be a hassle. Debug mode should log and results.
    | Debug logs should be disabled in any production environment!
    |
    */
    'debug' => env('YPA_ABSTRACT_API_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Disable mode
    |--------------------------------------------------------------------------
    |
    | Using this security can be a hassle. Disable mode can disable this
    | security to test your api requests fast and secure in your local setup.
    | Disable mode is not working in any production environment!
    |
    */
    'disable' => env('YPA_ABSTRACT_API_DISABLE', false)
];
