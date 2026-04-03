<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\CodeVault;
use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\SecureCode;

it('issues a code for an identifier', function () {
    $vault = SecureCode::vault();
    $code = $vault->issue('user@example.com');

    expect($code)
        ->toBeString()
        ->toHaveLength(6)
        ->toMatch('/^\d{6}$/');
});

it('verifies a valid code', function () {
    $vault = SecureCode::vault();
    $code = $vault->issue('user@example.com');

    expect($vault->verify('user@example.com', $code))->toBeTrue();
});

it('rejects an invalid code', function () {
    $vault = SecureCode::vault();
    $vault->issue('user@example.com');

    expect($vault->verify('user@example.com', '000000'))->toBeFalse();
});

it('revokes a code after successful verification', function () {
    $vault = SecureCode::vault();
    $code = $vault->issue('user@example.com');

    expect($vault->verify('user@example.com', $code))->toBeTrue();
    // Second verification should fail (code revoked)
    expect($vault->verify('user@example.com', $code))->toBeFalse();
});

it('manually revokes a code', function () {
    $vault = SecureCode::vault();
    $vault->issue('user@example.com');

    expect($vault->pending('user@example.com'))->toBeTrue();

    $vault->revoke('user@example.com');

    expect($vault->pending('user@example.com'))->toBeFalse();
});

it('tracks remaining attempts', function () {
    $vault = SecureCode::vault(maxAttempts: 3);
    $vault->issue('user@example.com');

    expect($vault->remainingAttempts('user@example.com'))->toBe(3);

    $vault->verify('user@example.com', 'wrong1');
    expect($vault->remainingAttempts('user@example.com'))->toBe(2);

    $vault->verify('user@example.com', 'wrong2');
    expect($vault->remainingAttempts('user@example.com'))->toBe(1);
});

it('locks out after max attempts exceeded', function () {
    $vault = SecureCode::vault(maxAttempts: 2);
    $code = $vault->issue('user@example.com');

    $vault->verify('user@example.com', 'wrong1');
    $vault->verify('user@example.com', 'wrong2');

    // Should be locked out now - code is deleted
    expect($vault->verify('user@example.com', $code))->toBeFalse();
    expect($vault->pending('user@example.com'))->toBeFalse();
});

it('returns zero remaining attempts for non-existent identifier', function () {
    $vault = SecureCode::vault();

    expect($vault->remainingAttempts('nonexistent@example.com'))->toBe(0);
});

it('creates vault with custom settings', function () {
    $vault = SecureCode::vault(length: 8, charset: Charset::AlphanumericUpper, ttl: 600, maxAttempts: 10);
    $code = $vault->issue('test');

    expect($code)
        ->toHaveLength(8)
        ->toMatch('/^[0-9A-Z]{8}$/');
});

it('creates vault directly', function () {
    $vault = new CodeVault(length: 4, charset: Charset::Numeric);
    $code = $vault->issue('test');

    expect($code)->toHaveLength(4)->toMatch('/^\d{4}$/');
});
