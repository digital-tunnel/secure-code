<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Support\Checksum;

it('appends a valid Luhn check digit', function () {
    $result = Checksum::appendLuhn('123456');

    expect(Checksum::verifyLuhn($result))->toBeTrue();
});

it('verifies known Luhn numbers', function () {
    expect(Checksum::verifyLuhn('79927398713'))->toBeTrue()
        ->and(Checksum::verifyLuhn('79927398710'))->toBeFalse();
});

it('rejects non-numeric strings for Luhn', function () {
    expect(Checksum::verifyLuhn('ABCD'))->toBeFalse()
        ->and(Checksum::verifyLuhn(''))->toBeFalse()
        ->and(Checksum::verifyLuhn('5'))->toBeFalse();
});

it('generates codes with Luhn checksum via builder', function () {
    $code = SecureCode::length(7)
        ->charset(Charset::Numeric)
        ->withChecksum('luhn')
        ->generate();

    // 7 digits + 1 check digit = 8
    expect($code)
        ->toBeString()
        ->toHaveLength(8)
        ->toMatch('/^\d{8}$/')
        ->and(Checksum::verifyLuhn($code))->toBeTrue();

});

it('generates batch with valid Luhn checksums', function () {
    $codes = SecureCode::length(7)
        ->charset(Charset::Numeric)
        ->withChecksum('luhn')
        ->count(10)
        ->generate();

    foreach ($codes as $code) {
        expect(Checksum::verifyLuhn($code))->toBeTrue();
    }
});

it('appends valid mod-97 check digits', function () {
    $result = Checksum::appendMod97('ABC123');

    expect(Checksum::verifyMod97($result))->toBeTrue();
});

it('verifies mod-97 correctly', function () {
    $code = Checksum::appendMod97('HELLO');

    expect(Checksum::verifyMod97($code))->toBeTrue()
        ->and(Checksum::verifyMod97($code.'0'))->toBeFalse();
});

it('rejects short strings for mod-97', function () {
    expect(Checksum::verifyMod97('AB'))->toBeFalse();
});

it('generates codes with mod-97 checksum via builder', function () {
    $code = SecureCode::length(6)
        ->charset(Charset::AlphanumericUpper)
        ->withChecksum('mod97')
        ->generate();

    expect($code)->toHaveLength(8)
        ->and(Checksum::verifyMod97($code))->toBeTrue();
});

it('verifies checksum via SecureCode static method', function () {
    $luhn = Checksum::appendLuhn('654321');

    expect(SecureCode::verifyChecksum($luhn, 'luhn'))->toBeTrue()
        ->and(SecureCode::verifyChecksum('123450', 'luhn'))->toBeFalse();
});
