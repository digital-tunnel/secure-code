<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Support\Export;

it('exports codes as JSON', function () {
    $json = SecureCode::length(6)->charset(Charset::Numeric)->count(3)->toJson();

    $decoded = json_decode($json, true);

    expect($decoded)
        ->toBeArray()
        ->toHaveCount(3);

    foreach ($decoded as $code) {
        expect($code)->toMatch('/^\d{6}$/');
    }
});

it('exports codes as pretty JSON', function () {
    $json = SecureCode::length(4)->count(2)->toJson(pretty: true);

    expect($json)->toContain("\n");
});

it('exports codes as CSV', function () {
    $csv = SecureCode::length(6)->charset(Charset::Numeric)->count(3)->toCsv();

    $lines = explode("\n", $csv);

    expect($lines[0])->toBe('code');
    expect($lines)->toHaveCount(4); // header + 3 codes
});

it('exports codes as CSV with custom header', function () {
    $csv = SecureCode::length(4)->count(2)->toCsv('voucher_code');

    expect(str_starts_with($csv, 'voucher_code'))->toBeTrue();
});

it('exports codes as plain text', function () {
    $text = SecureCode::length(6)->charset(Charset::Numeric)->count(3)->toText();

    $lines = explode("\n", $text);

    expect($lines)->toHaveCount(3);

    foreach ($lines as $line) {
        expect($line)->toMatch('/^\d{6}$/');
    }
});

it('exports single code as JSON array', function () {
    $json = SecureCode::length(8)->toJson();

    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()->toHaveCount(1);
});

it('works with Export class directly', function () {
    $codes = ['AAA', 'BBB', 'CCC'];

    expect(Export::toJson($codes))->toBe('["AAA","BBB","CCC"]');
    expect(Export::toCsv($codes))->toBe("code\nAAA\nBBB\nCCC");
    expect(Export::toText($codes))->toBe("AAA\nBBB\nCCC");
});
