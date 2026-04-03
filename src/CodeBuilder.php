<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode;

use Closure;
use DigitalTunnel\SecureCode\Contracts\UniquenessChecker;
use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\Enums\Preset;
use DigitalTunnel\SecureCode\Events\CodeBatchGenerated;
use DigitalTunnel\SecureCode\Events\CodeGenerated;
use DigitalTunnel\SecureCode\Support\Checksum;
use DigitalTunnel\SecureCode\Support\Entropy;
use DigitalTunnel\SecureCode\Support\Export;
use DigitalTunnel\SecureCode\Support\PatternGenerator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class CodeBuilder
{
    private int $length;

    private Charset $charset;

    private ?string $customPool = null;

    private string $prefix = '';

    private string $suffix = '';

    private ?string $separator = null;

    private ?int $separatorEvery = null;

    private bool $excludeSimilar;

    private ?bool $forceUppercase = null;

    private ?bool $forceLowercase = null;

    private int $count = 1;

    private Closure|UniquenessChecker|null $uniquenessChecker = null;

    private int $maxAttempts;

    private ?string $pattern = null;

    private ?string $checksumType = null;

    private bool $dispatchEvents = false;

    public function __construct()
    {
        $this->length = (int) $this->config('length', 8);
        $this->charset = $this->resolveCharset($this->config('charset', 'Alphanumeric'));
        $this->excludeSimilar = (bool) $this->config('exclude_similar', false);
        $this->maxAttempts = (int) $this->config('max_attempts', 1000);
    }

    // ─── Fluent Setters ─────────────────────────────────────────────

    public function length(int $length): self
    {
        $clone = clone $this;
        $clone->length = $length;

        return $clone;
    }

    public function charset(Charset $charset): self
    {
        $clone = clone $this;
        $clone->charset = $charset;
        $clone->customPool = null;

        return $clone;
    }

    public function pool(string $characters): self
    {
        $clone = clone $this;
        $clone->customPool = $characters;

        return $clone;
    }

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

    public function separator(string $separator, int $every = 4): self
    {
        $clone = clone $this;
        $clone->separator = $separator;
        $clone->separatorEvery = $every;

        return $clone;
    }

    public function uppercase(): self
    {
        $clone = clone $this;
        $clone->forceUppercase = true;
        $clone->forceLowercase = null;

        return $clone;
    }

    public function lowercase(): self
    {
        $clone = clone $this;
        $clone->forceLowercase = true;
        $clone->forceUppercase = null;

        return $clone;
    }

    public function excludeSimilar(bool $exclude = true): self
    {
        $clone = clone $this;
        $clone->excludeSimilar = $exclude;

        return $clone;
    }

    public function count(int $count): self
    {
        $clone = clone $this;
        $clone->count = max(1, $count);

        return $clone;
    }

    /**
     * @param  Closure(string): bool|UniquenessChecker  $checker
     */
    public function unique(Closure|UniquenessChecker $checker): self
    {
        $clone = clone $this;
        $clone->uniquenessChecker = $checker;

        return $clone;
    }

    public function maxAttempts(int $attempts): self
    {
        $clone = clone $this;
        $clone->maxAttempts = $attempts;

        return $clone;
    }

    /**
     * Apply a preset configuration.
     */
    public function preset(string|Preset $preset): self
    {
        if (is_string($preset)) {
            $preset = Preset::from($preset);
        }

        $config = $preset->config();
        $clone = clone $this;

        $clone->length = $config['length'];
        $clone->charset = $config['charset'];
        $clone->excludeSimilar = $config['exclude_similar'] ?? false;
        $clone->prefix = $config['prefix'] ?? '';

        if (isset($config['separator'])) {
            $clone->separator = $config['separator'];
            $clone->separatorEvery = $config['separator_every'] ?? 4;
        }

        if (isset($config['uppercase'])) {
            $clone->forceUppercase = $config['uppercase'];
        }

        if (isset($config['lowercase'])) {
            $clone->forceLowercase = $config['lowercase'];
        }

        return $clone;
    }

    /**
     * Set a pattern for generation (A=letter, a=lower, 9=digit, X=hex, *=any).
     */
    public function pattern(string $pattern): self
    {
        $clone = clone $this;
        $clone->pattern = $pattern;

        return $clone;
    }

    /**
     * Append a checksum digit to generated codes.
     */
    public function withChecksum(string $type = 'luhn'): self
    {
        $clone = clone $this;
        $clone->checksumType = $type;

        return $clone;
    }

    /**
     * Enable event dispatching on generation.
     */
    public function withEvents(bool $dispatch = true): self
    {
        $clone = clone $this;
        $clone->dispatchEvents = $dispatch;

        return $clone;
    }

    /**
     * Ensure uniqueness against a database table.
     */
    public function uniqueInTable(string $table, string $column = 'code', ?string $connection = null): self
    {
        return $this->unique(function (string $code) use ($table, $column, $connection): bool {
            $query = $connection ? DB::connection($connection)->table($table) : DB::table($table);

            return ! $query->where($column, $code)->exists();
        });
    }

    // ─── Generation ─────────────────────────────────────────────────

    /**
     * Generate code(s).
     */
    public function generate(): string|array
    {
        // Pattern-based generation
        if ($this->pattern !== null) {
            return $this->generateFromPattern();
        }

        $pool = $this->resolvePool();

        if ($this->count === 1 && $this->uniquenessChecker === null) {
            $code = $this->buildCode($pool);
            $this->fireEvents($code);

            return $code;
        }

        $codes = [];

        for ($i = 0; $i < $this->count; $i++) {
            $code = $this->generateUniqueCode($pool, $codes);
            $codes[] = $code;
        }

        $result = $this->count === 1 ? $codes[0] : $codes;
        $this->fireEvents($result);

        return $result;
    }

    // ─── Export ──────────────────────────────────────────────────────

    /**
     * Generate codes and export as JSON.
     */
    public function toJson(bool $pretty = false): string
    {
        $codes = $this->ensureArray();

        return Export::toJson($codes, $pretty);
    }

    /**
     * Generate codes and export as CSV.
     */
    public function toCsv(string $header = 'code'): string
    {
        $codes = $this->ensureArray();

        return Export::toCsv($codes, $header);
    }

    /**
     * Generate codes and export as plain text.
     */
    public function toText(): string
    {
        $codes = $this->ensureArray();

        return Export::toText($codes);
    }

    // ─── Analysis ───────────────────────────────────────────────────

    /**
     * Calculate the entropy of the current configuration.
     *
     * @return array{bits: float, strength: string, pool_size: int, length: int, combinations: string}
     */
    public function entropy(): array
    {
        $pool = $this->resolvePool();
        $poolSize = strlen($pool);
        $bits = Entropy::calculate($this->length, $poolSize);

        return [
            'bits' => $bits,
            'strength' => Entropy::strength($bits),
            'pool_size' => $poolSize,
            'length' => $this->length,
            'combinations' => bcpow((string) $poolSize, (string) $this->length),
        ];
    }

    // ─── Internals ──────────────────────────────────────────────────

    private function generateFromPattern(): string|array
    {
        if ($this->count === 1) {
            $code = PatternGenerator::generate($this->pattern);
            $this->fireEvents($code);

            return $code;
        }

        $codes = [];

        for ($i = 0; $i < $this->count; $i++) {
            $codes[] = PatternGenerator::generate($this->pattern);
        }

        $this->fireEvents($codes);

        return $codes;
    }

    private function buildCode(string $pool): string
    {
        $raw = CodeGenerator::randomFromPool($pool, $this->length);

        if ($this->forceUppercase) {
            $raw = strtoupper($raw);
        } elseif ($this->forceLowercase) {
            $raw = strtolower($raw);
        }

        // Append checksum before separator insertion
        if ($this->checksumType !== null) {
            $raw = match ($this->checksumType) {
                'luhn' => Checksum::appendLuhn($raw),
                'mod97' => Checksum::appendMod97($raw),
                default => $raw,
            };
        }

        if ($this->separator !== null && $this->separatorEvery !== null) {
            $raw = CodeGenerator::insertSeparators($raw, $this->separator, $this->separatorEvery);
        }

        return $this->prefix.$raw.$this->suffix;
    }

    private function generateUniqueCode(string $pool, array $existing): string
    {
        for ($attempt = 0; $attempt < $this->maxAttempts; $attempt++) {
            $code = $this->buildCode($pool);

            if (in_array($code, $existing, true)) {
                continue;
            }

            if ($this->uniquenessChecker !== null && ! $this->isUnique($code)) {
                continue;
            }

            return $code;
        }

        throw new RuntimeException(
            "Failed to generate a unique code after {$this->maxAttempts} attempts. "
            .'Consider increasing the code length or the character pool.'
        );
    }

    private function isUnique(string $code): bool
    {
        if ($this->uniquenessChecker instanceof UniquenessChecker) {
            return $this->uniquenessChecker->isUnique($code);
        }

        return ($this->uniquenessChecker)($code);
    }

    private function resolvePool(): string
    {
        if ($this->customPool !== null) {
            $pool = $this->customPool;
        } else {
            $pool = $this->charset->pool($this->excludeSimilar);
        }

        if ($pool === '') {
            throw new RuntimeException(
                'The resolved character pool is empty. This may happen when excludeSimilar '
                .'removes all characters from a small charset.'
            );
        }

        return $pool;
    }

    private function resolveCharset(string $name): Charset
    {
        foreach (Charset::cases() as $case) {
            if (strcasecmp($case->name, $name) === 0) {
                return $case;
            }
        }

        return Charset::Alphanumeric;
    }

    private function fireEvents(string|array $result): void
    {
        if (! $this->dispatchEvents) {
            return;
        }

        if (! function_exists('event')) {
            return;
        }

        if (is_array($result)) {
            event(new CodeBatchGenerated($result, count($result)));
        } else {
            event(new CodeGenerated($result));
        }
    }

    private function ensureArray(): array
    {
        $result = $this->generate();

        return is_array($result) ? $result : [$result];
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            return config("secure-code.{$key}", $default);
        }

        return $default;
    }
}
