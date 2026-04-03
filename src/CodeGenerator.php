<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode;

use RuntimeException;

final class CodeGenerator
{
    /**
     * Generate a single cryptographically secure random string from the given pool.
     */
    public static function randomFromPool(string $pool, int $length): string
    {
        $poolLength = strlen($pool);

        if ($poolLength === 0) {
            throw new RuntimeException('Character pool must not be empty.');
        }

        if ($length < 1) {
            throw new RuntimeException('Code length must be at least 1.');
        }

        $maxIndex = $poolLength - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $pool[random_int(0, $maxIndex)];
        }

        return $code;
    }

    /**
     * Insert a separator character into a string at regular intervals.
     */
    public static function insertSeparators(string $code, string $separator, int $every): string
    {
        if ($every < 1) {
            return $code;
        }

        return implode($separator, str_split($code, $every));
    }
}
