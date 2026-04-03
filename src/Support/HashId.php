<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Support;

use InvalidArgumentException;
use RuntimeException;

final class HashId
{
    private string $alphabet;

    private int $minLength;

    public function __construct(
        string $salt = '',
        string $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz',
        int $minLength = 6,
    ) {
        if (strlen($alphabet) < 16) {
            throw new InvalidArgumentException('Alphabet must have at least 16 characters.');
        }

        $this->alphabet = $this->shuffleAlphabet($alphabet, $salt);
        $this->minLength = max(1, $minLength);
    }

    /**
     * Encode an integer into a short obfuscated string.
     */
    public function encode(int $number): string
    {
        if ($number < 0) {
            throw new InvalidArgumentException('Cannot encode negative numbers.');
        }

        $base = strlen($this->alphabet);
        $encoded = '';

        do {
            $encoded = $this->alphabet[$number % $base].$encoded;
            $number = intdiv($number, $base);
        } while ($number > 0);

        // Pad to minimum length
        while (strlen($encoded) < $this->minLength) {
            $encoded = $this->alphabet[0].$encoded;
        }

        return $encoded;
    }

    /**
     * Decode a string back to the original integer.
     */
    public function decode(string $hash): int
    {
        $base = strlen($this->alphabet);
        $number = 0;

        for ($i = 0, $len = strlen($hash); $i < $len; $i++) {
            $pos = strpos($this->alphabet, $hash[$i]);

            if ($pos === false) {
                throw new RuntimeException("Invalid character '{$hash[$i]}' in hash.");
            }

            $number = $number * $base + $pos;
        }

        return $number;
    }

    /**
     * Shuffle the alphabet deterministically using the salt.
     */
    private function shuffleAlphabet(string $alphabet, string $salt): string
    {
        if ($salt === '') {
            return $alphabet;
        }

        $chars = str_split($alphabet);
        $saltLength = strlen($salt);
        $v = 0;
        $p = 0;

        for ($i = count($chars) - 1; $i > 0; $i--) {
            $v = $v % $saltLength;
            $a = ord($salt[$v]);
            $p += $a;
            $j = ($a + $v + $p) % $i;

            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
            $v++;
        }

        return implode('', $chars);
    }
}
