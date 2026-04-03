<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Support\Mask;

it('masks a code showing last 4 characters by default', function () {
    $masked = SecureCode::mask('ABCDEFGHIJKL');

    expect($masked)->toBe('********IJKL');
});

it('masks with custom visible end count', function () {
    $masked = SecureCode::mask('ABCDEFGH', visibleEnd: 2);

    expect($masked)->toBe('******GH');
});

it('masks with visible start', function () {
    $masked = SecureCode::mask('ABCDEFGH', visibleEnd: 0, visibleStart: 3);

    expect($masked)->toBe('ABC*****');
});

it('masks with both visible start and end', function () {
    $masked = SecureCode::mask('ABCDEFGHIJ', visibleEnd: 2, visibleStart: 2);

    expect($masked)->toBe('AB******IJ');
});

it('masks with custom masking character', function () {
    $masked = SecureCode::mask('ABCDEFGH', character: '#', visibleEnd: 4);

    expect($masked)->toBe('####EFGH');
});

it('preserves separator characters', function () {
    $masked = SecureCode::mask('ABCD-EFGH-IJKL', visibleEnd: 4, preserve: '-');

    expect($masked)->toBe('****-****-IJKL');
});

it('preserves multiple separator types', function () {
    $masked = SecureCode::mask('AB.CD-EF', visibleEnd: 2, preserve: '-.');

    expect($masked)->toBe('**.**-EF');
});

it('handles code shorter than visible portions', function () {
    $masked = SecureCode::mask('AB', visibleEnd: 4);

    expect($masked)->toBe('AB');
});

it('works with Mask class directly', function () {
    $masked = Mask::apply('1234567890', '*', 3, 2);

    expect($masked)->toBe('12*****890');
});
