<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Support;

final class Mask
{
    /**
     * Mask a code, revealing only a portion of it.
     *
     * @param  string  $code  The code to mask
     * @param  string  $character  The masking character
     * @param  int  $visibleEnd  Number of characters to reveal at the end
     * @param  int  $visibleStart  Number of characters to reveal at the start
     * @param  string  $preserve  Characters that should never be masked (e.g. "-" for separators)
     */
    public static function apply(
        string $code,
        string $character = '*',
        int $visibleEnd = 4,
        int $visibleStart = 0,
        string $preserve = '',
    ): string {
        $length = strlen($code);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $code[$i];

            if ($preserve !== '' && str_contains($preserve, $char)) {
                $result .= $char;

                continue;
            }

            if ($i < $visibleStart || $i >= $length - $visibleEnd) {
                $result .= $char;
            } else {
                $result .= $character;
            }
        }

        return $result;
    }
}
