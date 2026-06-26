<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    |
    | Don't forget to set this in your .env file, as it will be used to sign
    | your tokens. A helper command is provided for this:
    | `php artisan jwt:secret`
    |
    | The secret key used to sign the tokens.
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT hashing algorithm
    |--------------------------------------------------------------------------
    |
    | Set the algorithm used to sign the tokens.
    |
    | Supported algorithms: 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'
    |
    */

    'algo' => env('JWT_ALGORITHM', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | JWT time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token will be valid for.
    | Defaults to 1 hour.
    |
    */

    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Refresh time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token can be refreshed
    | within. I.E. The user can refresh their token within this time frame.
    |
    */

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT hashing key
    |--------------------------------------------------------------------------
    |
    | Used when 'algo' is set to 'RS256' or 'RS384' or 'RS512'.
    | The path to your public key file.
    |
    */

    'public_key' => env('JWT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | JWT hashing key
    |--------------------------------------------------------------------------
    |
    | Used when 'algo' is set to 'RS256' or 'RS384' or 'RS512'.
    | The path to your private key file.
    |
    */

    'private_key' => env('JWT_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | JWT Algorithm Keys
    |--------------------------------------------------------------------------
    |
    | The algorithm keys.
    |
    */

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
    ],

];
