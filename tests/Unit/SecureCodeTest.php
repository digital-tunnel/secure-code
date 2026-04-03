<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\CodeBuilder;
use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\SecureCode;

it('generates a code with default settings', function () {
    $code = SecureCode::generate();

    expect($code)
        ->toBeString()
        ->toHaveLength(8);
});

it('returns a CodeBuilder from static method calls', function () {
    $builder = SecureCode::length(10);

    expect($builder)->toBeInstanceOf(CodeBuilder::class);
});

it('generates a code with the specified length', function (int $length) {
    $code = SecureCode::length($length)->generate();

    expect($code)->toHaveLength($length);
})->with([1, 4, 6, 8, 16, 32, 64, 128]);

it('generates numeric-only codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Numeric)->generate();

    expect($code)->toMatch('/^\d{20}$/');
});

it('generates alpha-only codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Alpha)->generate();

    expect($code)->toMatch('/^[a-zA-Z]{20}$/');
});

it('generates uppercase alpha codes', function () {
    $code = SecureCode::length(20)->charset(Charset::AlphaUpper)->generate();

    expect($code)->toMatch('/^[A-Z]{20}$/');
});

it('generates lowercase alpha codes', function () {
    $code = SecureCode::length(20)->charset(Charset::AlphaLower)->generate();

    expect($code)->toMatch('/^[a-z]{20}$/');
});

it('generates alphanumeric codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Alphanumeric)->generate();

    expect($code)->toMatch('/^[0-9a-zA-Z]{20}$/');
});

it('generates uppercase alphanumeric codes', function () {
    $code = SecureCode::length(20)->charset(Charset::AlphanumericUpper)->generate();

    expect($code)->toMatch('/^[0-9A-Z]{20}$/');
});

it('generates lowercase alphanumeric codes', function () {
    $code = SecureCode::length(20)->charset(Charset::AlphanumericLower)->generate();

    expect($code)->toMatch('/^[0-9a-z]{20}$/');
});

it('generates hex codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Hex)->generate();

    expect($code)->toMatch('/^[0-9a-f]{20}$/');
});

it('generates uppercase hex codes', function () {
    $code = SecureCode::length(20)->charset(Charset::HexUpper)->generate();

    expect($code)->toMatch('/^[0-9A-F]{20}$/');
});

it('generates binary codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Binary)->generate();

    expect($code)->toMatch('/^[01]{20}$/');
});

it('generates base32 codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Base32)->generate();

    expect($code)->toMatch('/^[A-Z2-7]{20}$/');
});

it('generates base58 codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Base58)->generate();

    // Base58 excludes 0, O, I, l
    expect($code)->toMatch('/^[1-9A-HJ-NP-Za-km-z]{20}$/');
});

it('generates base64-safe codes', function () {
    $code = SecureCode::length(20)->charset(Charset::Base64Safe)->generate();

    expect($code)->toMatch('/^[A-Za-z0-9\-_]{20}$/');
});

it('generates codes from a custom character pool', function () {
    $code = SecureCode::pool('ABC')->length(20)->generate();

    expect($code)->toMatch('/^[ABC]{20}$/');
});

it('prepends a prefix', function () {
    $code = SecureCode::length(6)->charset(Charset::Numeric)->prefix('INV-')->generate();

    expect($code)
        ->toStartWith('INV-')
        ->toHaveLength(10);
});

it('appends a suffix', function () {
    $code = SecureCode::length(6)->charset(Charset::Numeric)->suffix('-2026')->generate();

    expect($code)
        ->toEndWith('-2026')
        ->toHaveLength(11);
});

it('applies both prefix and suffix', function () {
    $code = SecureCode::length(4)->charset(Charset::Numeric)->prefix('A-')->suffix('-Z')->generate();

    expect($code)
        ->toStartWith('A-')
        ->toEndWith('-Z')
        ->toHaveLength(8);
});

it('inserts separators at the specified interval', function () {
    $code = SecureCode::length(12)->charset(Charset::AlphanumericUpper)->separator('-', 4)->generate();

    expect($code)
        ->toHaveLength(14)
        ->toMatch('/^[0-9A-Z]{4}-[0-9A-Z]{4}-[0-9A-Z]{4}$/');
});

it('inserts separators with custom character', function () {
    $code = SecureCode::length(8)->charset(Charset::Numeric)->separator(' ', 4)->generate();

    expect($code)->toMatch('/^\d{4} \d{4}$/');
});

it('forces uppercase output', function () {
    $code = SecureCode::length(20)->charset(Charset::Alpha)->uppercase()->generate();

    expect($code)->toMatch('/^[A-Z]{20}$/');
});

it('forces lowercase output', function () {
    $code = SecureCode::length(20)->charset(Charset::Alpha)->lowercase()->generate();

    expect($code)->toMatch('/^[a-z]{20}$/');
});

it('excludes visually similar characters', function () {
    for ($i = 0; $i < 50; $i++) {
        $code = SecureCode::length(100)->charset(Charset::Alphanumeric)->excludeSimilar()->generate();

        expect($code)->not->toMatch('/[0O1Il]/');
    }
});

it('generates a batch of codes', function () {
    $codes = SecureCode::length(8)->count(10)->generate();

    expect($codes)
        ->toBeArray()
        ->toHaveCount(10);

    foreach ($codes as $code) {
        expect($code)->toBeString()->toHaveLength(8);
    }
});

it('returns a string when count is 1', function () {
    $code = SecureCode::length(8)->count(1)->generate();

    expect($code)->toBeString();
});

it('generates unique codes within a batch', function () {
    $codes = SecureCode::length(16)->count(100)->generate();

    expect($codes)->toHaveCount(100)
        ->and(array_unique($codes))->toHaveCount(100);
});

it('uses a closure uniqueness checker', function () {
    $existing = ['AAAA', 'BBBB'];

    $code = SecureCode::length(4)
        ->charset(Charset::AlphanumericUpper)
        ->unique(fn (string $code) => ! in_array($code, $existing, true))
        ->generate();

    expect($code)
        ->toBeString()
        ->not->toBe('AAAA')
        ->not->toBe('BBBB');
});

it('uses a UniquenessChecker interface', function () {
    $checker = new class implements \DigitalTunnel\SecureCode\Contracts\UniquenessChecker
    {
        public function isUnique(string $code): bool
        {
            return $code !== 'TAKEN';
        }
    };

    $code = SecureCode::length(8)->unique($checker)->generate();

    expect($code)->not->toBe('TAKEN');
});

it('throws when uniqueness cannot be satisfied', function () {
    SecureCode::pool('A')
        ->length(1)
        ->unique(fn () => false)
        ->maxAttempts(10)
        ->generate();
})->throws(RuntimeException::class, 'Failed to generate a unique code');

it('returns a new instance on each fluent call (immutable)', function () {
    $a = SecureCode::length(8);
    $b = $a->length(16);

    expect($a)->not->toBe($b);

    $codeA = $a->charset(Charset::Numeric)->generate();
    $codeB = $b->charset(Charset::Numeric)->generate();

    expect($codeA)->toHaveLength(8)
        ->and($codeB)->toHaveLength(16);
});

it('generates a single character code', function () {
    $code = SecureCode::length(1)->generate();

    expect($code)->toHaveLength(1);
});

it('throws on empty custom pool', function () {
    SecureCode::pool('')->length(4)->generate();
})->throws(RuntimeException::class, 'The resolved character pool is empty');

it('handles separators with non-divisible length', function () {
    $code = SecureCode::length(7)->charset(Charset::AlphaUpper)->separator('-', 3)->generate();

    expect($code)
        ->toHaveLength(9) // 7 chars + 2 dashes
        ->toMatch('/^[A-Z]{3}-[A-Z]{3}-[A-Z]$/');
});

it('combines all options together', function () {
    $code = SecureCode::length(8)
        ->charset(Charset::AlphanumericUpper)
        ->excludeSimilar()
        ->separator('-', 4)
        ->prefix('PRE-')
        ->suffix('-END')
        ->generate();

    expect($code)
        ->toStartWith('PRE-')
        ->toEndWith('-END');

    $inner = substr($code, 4, -4); // "XXXX-XXXX"
    expect($inner)->toMatch('/^[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}$/');
});
