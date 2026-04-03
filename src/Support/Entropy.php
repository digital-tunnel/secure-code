<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Support;

final class Entropy
{
    /**
     * Calculate the entropy in bits for a code of the given length and pool size.
     *
     * Entropy = length * log2(poolSize)
     */
    public static function calculate(int $length, int $poolSize): float
    {
        if ($poolSize < 2 || $length < 1) {
            return 0.0;
        }

        return round($length * log($poolSize, 2), 2);
    }

    /**
     * Return a human-readable strength label based on entropy bits.
     */
    public static function strength(float $bits): string
    {
        return match (true) {
            $bits >= 128 => 'very strong',
            $bits >= 80 => 'strong',
            $bits >= 48 => 'moderate',
            $bits >= 28 => 'weak',
            default => 'very weak',
        };
    }
}
