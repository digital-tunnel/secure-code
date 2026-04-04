<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Facades;

use DigitalTunnel\SecureCode\CodeBuilder;
use DigitalTunnel\SecureCode\CodeVault;
use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\Enums\Preset;
use DigitalTunnel\SecureCode\Sequence\SequenceBuilder;
use DigitalTunnel\SecureCode\Support\HashId;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string|array generate()
 * @method static CodeBuilder length(int $length)
 * @method static CodeBuilder charset(Charset $charset)
 * @method static CodeBuilder pool(string $characters)
 * @method static CodeBuilder prefix(string $prefix)
 * @method static CodeBuilder suffix(string $suffix)
 * @method static CodeBuilder separator(string $separator, int $every = 4)
 * @method static CodeBuilder uppercase()
 * @method static CodeBuilder lowercase()
 * @method static CodeBuilder excludeSimilar(bool $exclude = true)
 * @method static CodeBuilder count(int $count)
 * @method static CodeBuilder unique(\Closure|\DigitalTunnel\SecureCode\Contracts\UniquenessChecker $checker)
 * @method static CodeBuilder maxAttempts(int $attempts)
 * @method static CodeBuilder preset(string|Preset $preset)
 * @method static CodeBuilder pattern(string $pattern)
 * @method static CodeBuilder withChecksum(string $type = 'luhn')
 * @method static CodeBuilder withEvents(bool $dispatch = true)
 * @method static CodeBuilder uniqueInTable(string $table, string $column = 'code', ?string $connection = null)
 * @method static string mask(string $code, string $character = '*', int $visibleEnd = 4, int $visibleStart = 0, string $preserve = '')
 * @method static bool verifyChecksum(string $code, string $type = 'luhn')
 * @method static array entropy(int $length = null, Charset|string $charset = null)
 * @method static CodeVault vault(int $length = 6, Charset $charset = Charset::Numeric, int $ttl = 300, int $maxAttempts = 5)
 * @method static HashId hashid(string $salt = '', int $minLength = 6)
 * @method static SequenceBuilder sequence(string $key)
 *
 * @see \DigitalTunnel\SecureCode\SecureCode
 */
class SecureCode extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DigitalTunnel\SecureCode\SecureCode::class;
    }
}
