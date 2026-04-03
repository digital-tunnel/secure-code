<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Support;

use DigitalTunnel\SecureCode\CodeGenerator;

final class PatternGenerator
{
    private const LETTER_POOL = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private const LOWER_LETTER_POOL = 'abcdefghijklmnopqrstuvwxyz';

    private const DIGIT_POOL = '0123456789';

    private const HEX_POOL = '0123456789ABCDEF';

    private const ALPHANUMERIC_POOL = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * Generate a code from a pattern string.
     *
     * Placeholders:
     *   A = uppercase letter (A-Z)
     *   a = lowercase letter (a-z)
     *   9 = digit (0-9)
     *   X = uppercase hex (0-9, A-F)
     *   * = any alphanumeric character
     *
     * All other characters are kept as literal separators.
     */
    public static function generate(string $pattern): string
    {
        $result = '';

        for ($i = 0, $len = strlen($pattern); $i < $len; $i++) {
            $char = $pattern[$i];

            $result .= match ($char) {
                'A' => CodeGenerator::randomFromPool(self::LETTER_POOL, 1),
                'a' => CodeGenerator::randomFromPool(self::LOWER_LETTER_POOL, 1),
                '9' => CodeGenerator::randomFromPool(self::DIGIT_POOL, 1),
                'X' => CodeGenerator::randomFromPool(self::HEX_POOL, 1),
                '*' => CodeGenerator::randomFromPool(self::ALPHANUMERIC_POOL, 1),
                default => $char,
            };
        }

        return $result;
    }

    /**
     * Build a regex pattern that validates codes generated from the given pattern.
     */
    public static function toRegex(string $pattern): string
    {
        $regex = '';

        for ($i = 0, $len = strlen($pattern); $i < $len; $i++) {
            $char = $pattern[$i];

            $regex .= match ($char) {
                'A' => '[A-Z]',
                'a' => '[a-z]',
                '9' => '[0-9]',
                'X' => '[0-9A-F]',
                '*' => '[0-9A-Za-z]',
                default => preg_quote($char, '/'),
            };
        }

        return '/^'.$regex.'$/';
    }
}
