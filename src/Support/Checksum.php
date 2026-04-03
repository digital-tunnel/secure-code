<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Support;

final class Checksum
{
    /**
     * Calculate the Luhn check digit for a numeric string.
     */
    public static function luhnDigit(string $number): int
    {
        $digits = array_map('intval', str_split(strrev($number)));
        $sum = 0;

        foreach ($digits as $i => $digit) {
            $doubled = $digit * (($i % 2 === 0) ? 2 : 1);
            $sum += ($doubled > 9) ? $doubled - 9 : $doubled;
        }

        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Append a Luhn check digit to a numeric string.
     */
    public static function appendLuhn(string $number): string
    {
        return $number.self::luhnDigit($number);
    }

    /**
     * Verify that a numeric string passes the Luhn check.
     */
    public static function verifyLuhn(string $number): bool
    {
        if (strlen($number) < 2 || ! ctype_digit($number)) {
            return false;
        }

        $payload = substr($number, 0, -1);
        $checkDigit = (int) $number[strlen($number) - 1];

        return self::luhnDigit($payload) === $checkDigit;
    }

    /**
     * Calculate a mod-97 check value (two digits) for an alphanumeric string.
     * Used by IBAN-style validation.
     */
    public static function mod97Digits(string $code): string
    {
        $numericString = '';

        foreach (str_split(strtoupper($code)) as $char) {
            if (ctype_alpha($char)) {
                $numericString .= (string) (ord($char) - 55);
            } else {
                $numericString .= $char;
            }
        }

        $remainder = (int) bcmod($numericString.'00', '97');
        $check = 98 - $remainder;

        return str_pad((string) $check, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Append mod-97 check digits to an alphanumeric string.
     */
    public static function appendMod97(string $code): string
    {
        return $code.self::mod97Digits($code);
    }

    /**
     * Verify that an alphanumeric string passes the mod-97 check.
     */
    public static function verifyMod97(string $code): bool
    {
        if (strlen($code) < 3) {
            return false;
        }

        $payload = substr($code, 0, -2);
        $checkDigits = substr($code, -2);

        return self::mod97Digits($payload) === $checkDigits;
    }
}
