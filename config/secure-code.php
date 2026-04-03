<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Code Length
    |--------------------------------------------------------------------------
    |
    | The number of random characters to generate (excluding prefix, suffix,
    | and separators). Must be at least 1.
    |
    */

    'length' => 8,

    /*
    |--------------------------------------------------------------------------
    | Default Charset
    |--------------------------------------------------------------------------
    |
    | The character set used for generation. Accepts any case name from
    | DigitalTunnel\SecureCode\Enums\Charset (e.g. "Alphanumeric").
    |
    */

    'charset' => 'Alphanumeric',

    /*
    |--------------------------------------------------------------------------
    | Exclude Similar Characters
    |--------------------------------------------------------------------------
    |
    | When true, visually ambiguous characters (0, O, 1, I, l) are stripped
    | from the pool before generation. Useful for codes humans must read.
    |
    */

    'exclude_similar' => false,

    /*
    |--------------------------------------------------------------------------
    | Max Uniqueness Attempts
    |--------------------------------------------------------------------------
    |
    | When generating unique codes with a checker callback, this limits the
    | number of retries per code before throwing an exception.
    |
    */

    'max_attempts' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Vault Settings
    |--------------------------------------------------------------------------
    |
    | Default configuration for the CodeVault (TTL-based code issuance).
    |
    */

    'vault' => [
        'ttl' => 300,
        'max_attempts' => 5,
        'cache_prefix' => 'secure_code_vault:',
    ],

    /*
    |--------------------------------------------------------------------------
    | HashId Settings
    |--------------------------------------------------------------------------
    |
    | Default salt and minimum length for the HashId encoder.
    |
    */

    'hashid' => [
        'salt' => '',
        'min_length' => 6,
    ],

];
