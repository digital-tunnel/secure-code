<?php

declare(strict_types=1);

use DigitalTunnel\SecureCode\Events\CodeBatchGenerated;
use DigitalTunnel\SecureCode\Events\CodeGenerated;
use DigitalTunnel\SecureCode\SecureCode;
use Illuminate\Support\Facades\Event;

it('dispatches CodeGenerated event for single code', function () {
    Event::fake([CodeGenerated::class]);

    SecureCode::length(8)->withEvents()->generate();

    Event::assertDispatched(CodeGenerated::class, function (CodeGenerated $event) {
        return strlen($event->code) === 8;
    });
});

it('dispatches CodeBatchGenerated event for batch', function () {
    Event::fake([CodeBatchGenerated::class]);

    SecureCode::length(8)->count(5)->withEvents()->generate();

    Event::assertDispatched(CodeBatchGenerated::class, function (CodeBatchGenerated $event) {
        return $event->count === 5 && count($event->codes) === 5;
    });
});

it('does not dispatch events by default', function () {
    Event::fake([CodeGenerated::class, CodeBatchGenerated::class]);

    SecureCode::length(8)->generate();

    Event::assertNotDispatched(CodeGenerated::class);
    Event::assertNotDispatched(CodeBatchGenerated::class);
});

it('dispatches event for pattern-based generation', function () {
    Event::fake([CodeGenerated::class]);

    SecureCode::pattern('AAA-999')->withEvents()->generate();

    Event::assertDispatched(CodeGenerated::class);
});

it('dispatches batch event for pattern-based batch', function () {
    Event::fake([CodeBatchGenerated::class]);

    SecureCode::pattern('AAA-999')->count(3)->withEvents()->generate();

    Event::assertDispatched(CodeBatchGenerated::class, function (CodeBatchGenerated $event) {
        return $event->count === 3;
    });
});
