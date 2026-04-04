<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Sequence\SequenceBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $migration = include __DIR__.'/../../database/migrations/create_secure_code_sequences_table.php';
    $migration->up();
});

it('returns a SequenceBuilder from SecureCode::sequence()', function () {
    expect(SecureCode::sequence('test'))->toBeInstanceOf(SequenceBuilder::class);
});

it('generates a simple sequential ID', function () {
    $id = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->padSequence(5)
        ->next();

    expect($id)->toBe('INV-00001');
});

it('generates with full format including date', function () {
    $date = new DateTimeImmutable('2026-04-04');

    $id = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->suffix('-EG')
        ->format('{prefix}{sequence}{separator}{Y}{m}{d}{suffix}')
        ->padSequence(5)
        ->date($date)
        ->next();

    expect($id)->toBe('INV-00001-20260404-EG');
});

it('increments on subsequent calls', function () {
    $builder = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->padSequence(5);

    $first = $builder->next();
    $second = $builder->next();
    $third = $builder->next();

    expect($first)->toBe('INV-00001');
    expect($second)->toBe('INV-00002');
    expect($third)->toBe('INV-00003');
});

it('generates batch of contiguous IDs', function () {
    $ids = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->padSequence(3)
        ->next(3);

    expect($ids)->toBe(['INV-001', 'INV-002', 'INV-003']);
});

it('returns current value', function () {
    $builder = SecureCode::sequence('invoice');

    expect($builder->current())->toBeNull();

    $builder->next();
    expect($builder->current())->toBe(1);
});

it('previews next value without allocating', function () {
    $builder = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->padSequence(5);

    expect($builder->preview())->toBe('INV-00001');

    $builder->next();
    expect($builder->preview())->toBe('INV-00002');
});

it('resets sequence per year', function () {
    $builder = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->padSequence(3)
        ->resetEvery('yearly');

    $id2025 = $builder->date(new DateTimeImmutable('2025-12-31'))->next();
    $id2026 = $builder->date(new DateTimeImmutable('2026-01-01'))->next();

    expect($id2025)->toBe('INV-001');
    expect($id2026)->toBe('INV-001');
});

it('resets sequence per month', function () {
    $builder = SecureCode::sequence('invoice')
        ->padSequence(3)
        ->resetEvery('monthly');

    $builder->date(new DateTimeImmutable('2026-01-15'))->next();
    $builder->date(new DateTimeImmutable('2026-01-20'))->next();
    $id = $builder->date(new DateTimeImmutable('2026-02-01'))->next();

    expect($id)->toBe('001');
});

it('resets sequence per day', function () {
    $builder = SecureCode::sequence('daily-counter')
        ->padSequence(3)
        ->resetEvery('daily');

    $builder->date(new DateTimeImmutable('2026-04-04'))->next();
    $builder->date(new DateTimeImmutable('2026-04-04'))->next();
    $id = $builder->date(new DateTimeImmutable('2026-04-05'))->next();

    expect($id)->toBe('001');
});

it('supports custom start value', function () {
    $id = SecureCode::sequence('invoice')
        ->prefix('INV-')
        ->padSequence(5)
        ->startAt(1000)
        ->next();

    expect($id)->toBe('INV-01000');
});

it('is immutable (clone on every setter)', function () {
    $base = SecureCode::sequence('invoice')->padSequence(5);
    $withPrefix = $base->prefix('A-');
    $withOtherPrefix = $base->prefix('B-');

    $a = $withPrefix->next();
    $b = $withOtherPrefix->next();

    expect($a)->toBe('A-00001');
    expect($b)->toBe('B-00002');
});

it('keeps independent sequences separate', function () {
    SecureCode::sequence('invoice')->padSequence(3)->next();
    SecureCode::sequence('invoice')->padSequence(3)->next();

    $orderId = SecureCode::sequence('order')->padSequence(3)->next();

    expect($orderId)->toBe('001');
});

it('uses custom separator in format', function () {
    $date = new DateTimeImmutable('2026-04-04');

    $id = SecureCode::sequence('invoice')
        ->format('{sequence}{separator}{Y}')
        ->separator('/')
        ->padSequence(4)
        ->date($date)
        ->next();

    expect($id)->toBe('0001/2026');
});
