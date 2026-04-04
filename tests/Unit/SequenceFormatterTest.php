<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Sequence\SequenceFormatter;

it('formats with default template', function () {
    $result = SequenceFormatter::format(
        template: '{prefix}{sequence}{suffix}',
        sequence: 1,
        padWidth: 5,
        prefix: 'INV-',
        suffix: '-EG',
    );

    expect($result)->toBe('INV-00001-EG');
});

it('formats with year, month, day tokens', function () {
    $date = new DateTimeImmutable('2026-04-04');

    $result = SequenceFormatter::format(
        template: '{prefix}{sequence}-{Y}{m}{d}{suffix}',
        sequence: 42,
        padWidth: 5,
        prefix: 'INV-',
        suffix: '-EG',
        date: $date,
    );

    expect($result)->toBe('INV-00042-20260404-EG');
});

it('formats with 2-digit year token', function () {
    $date = new DateTimeImmutable('2026-12-25');

    $result = SequenceFormatter::format(
        template: '{sequence}-{y}{m}{d}',
        sequence: 1,
        padWidth: 3,
        date: $date,
    );

    expect($result)->toBe('001-261225');
});

it('formats with separator token', function () {
    $result = SequenceFormatter::format(
        template: '{prefix}{sequence}{separator}{Y}',
        sequence: 7,
        padWidth: 4,
        prefix: 'ORD',
        separator: '/',
        date: new DateTimeImmutable('2026-01-15'),
    );

    expect($result)->toBe('ORD0007/2026');
});

it('formats with timestamp token', function () {
    $date = new DateTimeImmutable('2026-01-01 00:00:00', new DateTimeZone('UTC'));

    $result = SequenceFormatter::format(
        template: '{sequence}-{timestamp}',
        sequence: 1,
        padWidth: 1,
        date: $date,
    );

    expect($result)->toBe('1-'.$date->getTimestamp());
});

it('handles empty prefix and suffix', function () {
    $result = SequenceFormatter::format(
        template: '{prefix}{sequence}{suffix}',
        sequence: 99,
        padWidth: 3,
    );

    expect($result)->toBe('099');
});

it('pads sequence to specified width', function () {
    $result = SequenceFormatter::format(
        template: '{sequence}',
        sequence: 5,
        padWidth: 8,
    );

    expect($result)->toBe('00000005');
});

it('does not truncate sequence exceeding pad width', function () {
    $result = SequenceFormatter::format(
        template: '{sequence}',
        sequence: 123456,
        padWidth: 3,
    );

    expect($result)->toBe('123456');
});

it('handles complex real-world format', function () {
    $date = new DateTimeImmutable('2026-04-04');

    $result = SequenceFormatter::format(
        template: '{prefix}{Y}{separator}{m}{separator}{d}{separator}{sequence}{suffix}',
        sequence: 1,
        padWidth: 6,
        prefix: 'RCP-',
        suffix: '-KW',
        separator: '-',
        date: $date,
    );

    expect($result)->toBe('RCP-2026-04-04-000001-KW');
});
