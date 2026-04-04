<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode;

use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\Enums\Preset;
use DigitalTunnel\SecureCode\Sequence\SequenceBuilder;
use DigitalTunnel\SecureCode\Sequence\SequenceGenerator;
use DigitalTunnel\SecureCode\Support\Checksum;
use DigitalTunnel\SecureCode\Support\HashId;
use DigitalTunnel\SecureCode\Support\Mask;

/**
 * @method static|CodeBuilder length(int $length)
 * @method static|CodeBuilder charset(Charset $charset)
 * @method static|CodeBuilder pool(string $characters)
 * @method static|CodeBuilder prefix(string $prefix)
 * @method static|CodeBuilder suffix(string $suffix)
 * @method static|CodeBuilder separator(string $separator, int $every = 4)
 * @method static|CodeBuilder uppercase()
 * @method static|CodeBuilder lowercase()
 * @method static|CodeBuilder excludeSimilar(bool $exclude = true)
 * @method static|CodeBuilder count(int $count)
 * @method static|CodeBuilder unique(\Closure|\DigitalTunnel\SecureCode\Contracts\UniquenessChecker $checker)
 * @method static|CodeBuilder maxAttempts(int $attempts)
 * @method static|CodeBuilder preset(string|Preset $preset)
 * @method static|CodeBuilder pattern(string $pattern)
 * @method static|CodeBuilder withChecksum(string $type = 'luhn')
 * @method static|CodeBuilder withEvents(bool $dispatch = true)
 * @method static|CodeBuilder uniqueInTable(string $table, string $column = 'code', ?string $connection = null)
 * @method static SequenceBuilder sequence(string $key)
 */
final class SecureCode
{
    /**
     * Generate a code using default settings.
     */
    public static function generate(): string|array
    {
        return (new CodeBuilder)->generate();
    }

    /**
     * Mask a code for display, hiding part of it.
     */
    public static function mask(
        string $code,
        string $character = '*',
        int $visibleEnd = 4,
        int $visibleStart = 0,
        string $preserve = '',
    ): string {
        return Mask::apply($code, $character, $visibleEnd, $visibleStart, $preserve);
    }

    /**
     * Verify a checksum on a code.
     */
    public static function verifyChecksum(string $code, string $type = 'luhn'): bool
    {
        return match ($type) {
            'luhn' => Checksum::verifyLuhn($code),
            'mod97' => Checksum::verifyMod97($code),
            default => false,
        };
    }

    /**
     * Create a new CodeVault instance for issuing and verifying codes with TTL.
     */
    public static function vault(
        int $length = 6,
        Charset $charset = Charset::Numeric,
        int $ttl = 300,
        int $maxAttempts = 5,
    ): CodeVault {
        return new CodeVault($length, $charset, $ttl, $maxAttempts);
    }

    /**
     * Create a new SequenceBuilder for generating sequential document IDs.
     */
    public static function sequence(string $key): SequenceBuilder
    {
        $generator = function_exists('app')
            ? app(SequenceGenerator::class)
            : new SequenceGenerator;

        return new SequenceBuilder($key, $generator);
    }

    /**
     * Create a new HashId instance for encoding/decoding integers.
     */
    public static function hashid(string $salt = '', int $minLength = 6): HashId
    {
        return new HashId($salt, minLength: $minLength);
    }

    /**
     * Forward static calls to a fresh CodeBuilder instance.
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return (new CodeBuilder)->{$method}(...$arguments);
    }

    /**
     * Forward instance calls to a fresh CodeBuilder instance.
     * Required for Laravel Facade resolution which calls instance methods.
     */
    public function __call(string $method, array $arguments): mixed
    {
        return (new CodeBuilder)->{$method}(...$arguments);
    }
}
