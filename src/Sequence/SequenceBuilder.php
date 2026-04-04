<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Sequence;

use DigitalTunnel\SecureCode\Enums\SequenceResetPeriod;

final class SequenceBuilder
{
    private string $prefix = '';

    private string $suffix = '';

    private string $separator = '-';

    private string $format;

    private int $padWidth;

    private SequenceResetPeriod $resetPeriod;

    private int $startAt;

    private ?string $connection = null;

    private ?\DateTimeInterface $date = null;

    public function __construct(
        private readonly string $key,
        private readonly SequenceGenerator $generator,
    ) {
        $this->format = $this->config('sequences.format', '{prefix}{sequence}{suffix}');
        $this->padWidth = (int) $this->config('sequences.pad', 5);
        $this->startAt = (int) $this->config('sequences.start_at', 1);
        $this->resetPeriod = $this->resolveResetPeriod($this->config('sequences.reset', 'never'));
    }

    // ─── Fluent Setters ─────────────────────────────────────────────

    public function prefix(string $prefix): self
    {
        $clone = clone $this;
        $clone->prefix = $prefix;

        return $clone;
    }

    public function suffix(string $suffix): self
    {
        $clone = clone $this;
        $clone->suffix = $suffix;

        return $clone;
    }

    public function separator(string $separator): self
    {
        $clone = clone $this;
        $clone->separator = $separator;

        return $clone;
    }

    public function format(string $format): self
    {
        $clone = clone $this;
        $clone->format = $format;

        return $clone;
    }

    public function padSequence(int $width): self
    {
        $clone = clone $this;
        $clone->padWidth = $width;

        return $clone;
    }

    public function resetEvery(string|SequenceResetPeriod $period): self
    {
        $clone = clone $this;
        $clone->resetPeriod = $period instanceof SequenceResetPeriod
            ? $period
            : SequenceResetPeriod::from($period);

        return $clone;
    }

    public function startAt(int $value): self
    {
        $clone = clone $this;
        $clone->startAt = $value;

        return $clone;
    }

    public function connection(string $connection): self
    {
        $clone = clone $this;
        $clone->connection = $connection;

        return $clone;
    }

    public function date(\DateTimeInterface $date): self
    {
        $clone = clone $this;
        $clone->date = $date;

        return $clone;
    }

    // ─── Terminal Methods ───────────────────────────────────────────

    /**
     * Allocate and return the next sequential ID(s).
     *
     * @return string|string[] A single formatted ID, or an array when $count > 1
     */
    public function next(int $count = 1): string|array
    {
        $date = $this->date ?? new \DateTimeImmutable;
        $periodKey = $this->resetPeriod->periodKey($date);

        $values = $this->generator->allocate(
            key: $this->key,
            periodKey: $periodKey,
            count: $count,
            startAt: $this->startAt,
            connection: $this->connection,
        );

        $ids = array_map(
            fn (int $value) => $this->formatValue($value, $date),
            $values,
        );

        return $count === 1 ? $ids[0] : $ids;
    }

    /**
     * Get the current (last allocated) sequence value without incrementing.
     */
    public function current(): ?int
    {
        $date = $this->date ?? new \DateTimeImmutable;
        $periodKey = $this->resetPeriod->periodKey($date);

        return $this->generator->current(
            key: $this->key,
            periodKey: $periodKey,
            connection: $this->connection,
        );
    }

    /**
     * Preview what the next ID would look like without allocating a number.
     */
    public function preview(): string
    {
        $date = $this->date ?? new \DateTimeImmutable;
        $periodKey = $this->resetPeriod->periodKey($date);

        $currentValue = $this->generator->current(
            key: $this->key,
            periodKey: $periodKey,
            connection: $this->connection,
        );

        $nextValue = $currentValue !== null ? $currentValue + 1 : $this->startAt;

        return $this->formatValue($nextValue, $date);
    }

    // ─── Internals ──────────────────────────────────────────────────

    private function formatValue(int $sequence, \DateTimeInterface $date): string
    {
        return SequenceFormatter::format(
            template: $this->format,
            sequence: $sequence,
            padWidth: $this->padWidth,
            prefix: $this->prefix,
            suffix: $this->suffix,
            separator: $this->separator,
            date: $date,
        );
    }

    private function resolveResetPeriod(string $value): SequenceResetPeriod
    {
        return SequenceResetPeriod::tryFrom($value) ?? SequenceResetPeriod::Never;
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            return config("secure-code.{$key}", $default);
        }

        return $default;
    }
}
