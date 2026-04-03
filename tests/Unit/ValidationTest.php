<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\Rules\SecureCodeFormat;
use DigitalTunnel\SecureCode\Support\Checksum;
use Illuminate\Support\Facades\Validator;

it('validates code length', function () {
    $rule = new SecureCodeFormat(length: 6);

    $valid = Validator::make(['code' => 'ABCDEF'], ['code' => $rule]);
    $invalid = Validator::make(['code' => 'ABC'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($invalid->passes())->toBeFalse();
});

it('validates code charset', function () {
    $rule = new SecureCodeFormat(charset: Charset::Numeric);

    $valid = Validator::make(['code' => '123456'], ['code' => $rule]);
    $invalid = Validator::make(['code' => 'ABCDEF'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($invalid->passes())->toBeFalse();
});

it('validates both length and charset', function () {
    $rule = new SecureCodeFormat(length: 4, charset: Charset::AlphaUpper);

    $valid = Validator::make(['code' => 'ABCD'], ['code' => $rule]);
    $wrongLength = Validator::make(['code' => 'ABC'], ['code' => $rule]);
    $wrongCharset = Validator::make(['code' => 'ab12'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($wrongLength->passes())->toBeFalse();
    expect($wrongCharset->passes())->toBeFalse();
});

it('validates pattern-based codes', function () {
    $rule = new SecureCodeFormat(pattern: 'AAA-999');

    $valid = Validator::make(['code' => 'ABC-123'], ['code' => $rule]);
    $invalid = Validator::make(['code' => '123-ABC'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($invalid->passes())->toBeFalse();
});

it('validates Luhn checksum', function () {
    $code = Checksum::appendLuhn('123456');
    $rule = new SecureCodeFormat(verifyChecksum: true, checksumType: 'luhn');

    $valid = Validator::make(['code' => $code], ['code' => $rule]);
    $invalid = Validator::make(['code' => '1234560'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($invalid->passes())->toBeFalse();
});

it('validates mod-97 checksum', function () {
    $code = Checksum::appendMod97('HELLO');
    $rule = new SecureCodeFormat(verifyChecksum: true, checksumType: 'mod97');

    $valid = Validator::make(['code' => $code], ['code' => $rule]);
    $invalid = Validator::make(['code' => 'HELLO99'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($invalid->passes())->toBeFalse();
});

it('rejects non-string values', function () {
    $rule = new SecureCodeFormat(length: 4);

    $invalid = Validator::make(['code' => 1234], ['code' => $rule]);

    expect($invalid->passes())->toBeFalse();
});

it('validates hex charset', function () {
    $rule = new SecureCodeFormat(length: 8, charset: Charset::HexUpper);

    $valid = Validator::make(['code' => '3A7F1B9E'], ['code' => $rule]);
    $invalid = Validator::make(['code' => '3A7F1BZZ'], ['code' => $rule]);

    expect($valid->passes())->toBeTrue();
    expect($invalid->passes())->toBeFalse();
});
