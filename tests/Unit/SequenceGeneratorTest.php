<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Sequence\SequenceGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->generator = new SequenceGenerator;

    // Run the package migration
    $migration = include __DIR__.'/../../database/migrations/create_secure_code_sequences_table.php';
    $migration->up();
});

it('allocates first sequence value starting at 1', function () {
    $values = $this->generator->allocate('invoice');

    expect($values)->toBe([1]);
});

it('increments on subsequent calls', function () {
    $this->generator->allocate('invoice');
    $this->generator->allocate('invoice');
    $values = $this->generator->allocate('invoice');

    expect($values)->toBe([3]);
});

it('allocates a batch of contiguous values', function () {
    $values = $this->generator->allocate('invoice', count: 5);

    expect($values)->toBe([1, 2, 3, 4, 5]);
});

it('continues after batch allocation', function () {
    $this->generator->allocate('invoice', count: 3);
    $values = $this->generator->allocate('invoice', count: 2);

    expect($values)->toBe([4, 5]);
});

it('supports custom start value', function () {
    $values = $this->generator->allocate('invoice', startAt: 1000);

    expect($values)->toBe([1000]);
});

it('uses custom start only for first allocation', function () {
    $this->generator->allocate('invoice', startAt: 1000);
    $values = $this->generator->allocate('invoice', startAt: 1000);

    expect($values)->toBe([1001]);
});

it('keeps independent sequences separate', function () {
    $this->generator->allocate('invoice');
    $this->generator->allocate('invoice');
    $orderValues = $this->generator->allocate('order');

    expect($orderValues)->toBe([1]);
});

it('handles period-based separation', function () {
    $this->generator->allocate('invoice', periodKey: '2025');
    $this->generator->allocate('invoice', periodKey: '2025');

    $values2026 = $this->generator->allocate('invoice', periodKey: '2026');

    expect($values2026)->toBe([1]);
});

it('returns current value', function () {
    expect($this->generator->current('invoice'))->toBeNull();

    $this->generator->allocate('invoice');
    expect($this->generator->current('invoice'))->toBe(1);

    $this->generator->allocate('invoice', count: 5);
    expect($this->generator->current('invoice'))->toBe(6);
});

it('returns current for specific period', function () {
    $this->generator->allocate('invoice', periodKey: '2025', count: 3);
    $this->generator->allocate('invoice', periodKey: '2026', count: 7);

    expect($this->generator->current('invoice', periodKey: '2025'))->toBe(3);
    expect($this->generator->current('invoice', periodKey: '2026'))->toBe(7);
});
