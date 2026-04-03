<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Support\Entropy;

it('calculates entropy for alphanumeric charset', function () {
    $result = SecureCode::length(8)->charset(Charset::Alphanumeric)->entropy();

    expect($result)
        ->toBeArray()
        ->toHaveKeys(['bits', 'strength', 'pool_size', 'length', 'combinations'])
        ->and($result['pool_size'])->toBe(62)
        ->and($result['length'])->toBe(8)
        ->and($result['bits'])->toBeGreaterThan(47.0)
        ->and($result['strength'])->toBeIn(['moderate', 'weak']);

});

it('calculates entropy for numeric charset', function () {
    $result = SecureCode::length(6)->charset(Charset::Numeric)->entropy();

    expect($result['pool_size'])->toBe(10)
        ->and($result['bits'])->toBeGreaterThan(19.0)
        ->and($result['strength'])->toBe('very weak');
});

it('calculates entropy for long token', function () {
    $result = SecureCode::length(64)->charset(Charset::Alphanumeric)->entropy();

    expect($result['bits'])->toBeGreaterThan(380.0)
        ->and($result['strength'])->toBe('very strong');
});

it('returns strength labels correctly', function () {
    expect(Entropy::strength(10))->toBe('very weak')
        ->and(Entropy::strength(30))->toBe('weak')
        ->and(Entropy::strength(50))->toBe('moderate')
        ->and(Entropy::strength(90))->toBe('strong')
        ->and(Entropy::strength(130))->toBe('very strong');
});

it('calculates zero entropy for invalid input', function () {
    expect(Entropy::calculate(0, 62))->toBe(0.0)
        ->and(Entropy::calculate(8, 1))->toBe(0.0);
});

it('includes combination count', function () {
    $result = SecureCode::length(4)->charset(Charset::Numeric)->entropy();

    expect($result['combinations'])->toBe('10000');
});
