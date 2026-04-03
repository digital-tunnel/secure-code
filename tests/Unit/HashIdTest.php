<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Support\HashId;

it('encodes and decodes an integer', function () {
    $hashid = SecureCode::hashid();

    $encoded = $hashid->encode(12345);
    $decoded = $hashid->decode($encoded);

    expect($decoded)->toBe(12345);
});

it('encodes zero', function () {
    $hashid = SecureCode::hashid();

    $encoded = $hashid->encode(0);
    $decoded = $hashid->decode($encoded);

    expect($decoded)->toBe(0);
});

it('respects minimum length', function () {
    $hashid = SecureCode::hashid(minLength: 10);

    $encoded = $hashid->encode(1);

    expect(strlen($encoded))->toBeGreaterThanOrEqual(10);
    expect($hashid->decode($encoded))->toBe(1);
});

it('produces different results with different salts', function () {
    $hashid1 = SecureCode::hashid(salt: 'salt-one');
    $hashid2 = SecureCode::hashid(salt: 'salt-two');

    $encoded1 = $hashid1->encode(999);
    $encoded2 = $hashid2->encode(999);

    expect($encoded1)->not->toBe($encoded2);
});

it('roundtrips large numbers', function () {
    $hashid = SecureCode::hashid();

    foreach ([1, 100, 9999, 123456, 999999999] as $number) {
        expect($hashid->decode($hashid->encode($number)))->toBe($number);
    }
});

it('throws on negative numbers', function () {
    $hashid = SecureCode::hashid();
    $hashid->encode(-1);
})->throws(InvalidArgumentException::class);

it('throws on invalid characters during decode', function () {
    $hashid = SecureCode::hashid();
    $hashid->decode('!!!');
})->throws(RuntimeException::class);

it('works with custom alphabet', function () {
    $hashid = new HashId(alphabet: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', minLength: 4);

    $encoded = $hashid->encode(42);
    expect($encoded)->toMatch('/^[A-Z]+$/');
    expect($hashid->decode($encoded))->toBe(42);
});

it('throws when alphabet is too short', function () {
    new HashId(alphabet: 'ABC');
})->throws(InvalidArgumentException::class);
