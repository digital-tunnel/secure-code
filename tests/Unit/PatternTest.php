<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Support\PatternGenerator;

it('generates code from a simple letter-digit pattern', function () {
    $code = SecureCode::pattern('AAA-999-AAA')->generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(11)
        ->toMatch('/^[A-Z]{3}-\d{3}-[A-Z]{3}$/');
});

it('generates code with lowercase letters', function () {
    $code = SecureCode::pattern('aaa-999')->generate();

    expect($code)->toMatch('/^[a-z]{3}-\d{3}$/');
});

it('generates code with hex placeholders', function () {
    $code = SecureCode::pattern('XXXX-XXXX')->generate();

    expect($code)->toMatch('/^[0-9A-F]{4}-[0-9A-F]{4}$/');
});

it('generates code with wildcard placeholders', function () {
    $code = SecureCode::pattern('***-***')->generate();

    expect($code)->toMatch('/^[0-9A-Za-z]{3}-[0-9A-Za-z]{3}$/');
});

it('preserves literal characters in pattern', function () {
    $code = SecureCode::pattern('INV-999-AA')->generate();

    expect($code)
        ->toStartWith('INV-')
        ->toHaveLength(10);
});

it('generates a batch from pattern', function () {
    $codes = SecureCode::pattern('AA-99')->count(5)->generate();

    expect($codes)->toBeArray()->toHaveCount(5);

    foreach ($codes as $code) {
        expect($code)->toMatch('/^[A-Z]{2}-\d{2}$/');
    }
});

it('generates a regex from a pattern', function () {
    $regex = PatternGenerator::toRegex('AAA-999');

    expect($regex)->toBe('/^[A-Z][A-Z][A-Z]\-[0-9][0-9][0-9]$/');
});

it('generated regex validates matching codes', function () {
    $pattern = 'AA-99-XX';
    $code = SecureCode::pattern($pattern)->generate();
    $regex = PatternGenerator::toRegex($pattern);

    expect(preg_match($regex, $code))->toBe(1);
});

it('handles mixed placeholder types', function () {
    $code = SecureCode::pattern('Aa9X*')->generate();

    expect($code)->toHaveLength(5)
        ->and($code[0])->toMatch('/[A-Z]/')
        ->and($code[1])->toMatch('/[a-z]/')
        ->and($code[2])->toMatch('/[0-9]/')
        ->and($code[3])->toMatch('/[0-9A-F]/')
        ->and($code[4])->toMatch('/[0-9A-Za-z]/');
});
