<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Enums\Preset;
use DigitalTunnel\SecureCode\SecureCode;

it('generates a PIN preset (6 numeric digits)', function () {
    $code = SecureCode::preset('pin')->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(6)
        ->toMatch('/^\d{6}$/');
});

it('generates an OTP preset (6 numeric digits)', function () {
    $code = SecureCode::preset('otp')->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(6)
        ->toMatch('/^\d{6}$/');
});

it('generates a voucher preset (16 chars, uppercase, separated)', function () {
    $code = SecureCode::preset('voucher')->generate();

    // 16 chars + 3 dashes = 19 total
    expect($code)
        ->toBeString()
        ->toHaveLength(19)
        ->toContain('-');

    // Should not contain similar characters
    $stripped = str_replace('-', '', $code);
    expect($stripped)->not->toMatch('/[0O1Il]/');
});

it('generates a serial preset (20 hex chars, separated)', function () {
    $code = SecureCode::preset('serial')->generate();

    // 20 chars + 4 dashes = 24 total
    expect($code)
        ->toBeString()
        ->toHaveLength(24)
        ->toMatch('/^[0-9A-F]{4}(-[0-9A-F]{4}){4}$/');
});

it('generates an api-key preset (40 chars with prefix)', function () {
    $code = SecureCode::preset('api-key')->generate();

    expect($code)
        ->toBeString()
        ->toStartWith('sk_')
        ->and(strlen($code))->toBe(43);
});

it('generates a token preset (64 alphanumeric chars)', function () {
    $code = SecureCode::preset('token')->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(64)
        ->toMatch('/^[0-9a-zA-Z]{64}$/');
});

it('generates an invite preset (12 base58 chars)', function () {
    $code = SecureCode::preset('invite')->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(12)
        ->toMatch('/^[1-9A-HJ-NP-Za-km-z]{12}$/');
});

it('accepts a Preset enum directly', function () {
    $code = SecureCode::preset(Preset::Pin)->generate();

    expect($code)->toMatch('/^\d{6}$/');
});

it('can override preset options', function () {
    $code = SecureCode::preset('pin')->length(8)->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(8)
        ->toMatch('/^\d{8}$/');
});

it('generates a batch with presets', function () {
    $codes = SecureCode::preset('pin')->count(5)->generate();

    expect($codes)
        ->toBeArray()
        ->toHaveCount(5);

    foreach ($codes as $code) {
        expect($code)->toMatch('/^\d{6}$/');
    }
});
