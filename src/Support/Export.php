<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Support;

final class Export
{
    /**
     * Export codes as a JSON string.
     *
     * @param  array<int, string>  $codes
     */
    public static function toJson(array $codes, bool $pretty = false): string
    {
        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($codes, $flags);
    }

    /**
     * Export codes as a CSV string.
     *
     * @param  array<int, string>  $codes
     */
    public static function toCsv(array $codes, string $header = 'code'): string
    {
        $lines = [$header];

        foreach ($codes as $code) {
            $lines[] = $code;
        }

        return implode("\n", $lines);
    }

    /**
     * Export codes as a plain text list (one per line).
     *
     * @param  array<int, string>  $codes
     */
    public static function toText(array $codes): string
    {
        return implode("\n", $codes);
    }
}
