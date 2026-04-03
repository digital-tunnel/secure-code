<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Enums;

enum Charset: string
{
    case Numeric = '0123456789';
    case Alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    case AlphaUpper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    case AlphaLower = 'abcdefghijklmnopqrstuvwxyz';
    case Alphanumeric = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    case AlphanumericUpper = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    case AlphanumericLower = '0123456789abcdefghijklmnopqrstuvwxyz';
    case Hex = '0123456789abcdef';
    case HexUpper = '0123456789ABCDEF';
    case Binary = '01';
    case Base32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    case Base58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    case Base64Safe = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

    private const SIMILAR_CHARACTERS = '0O1Il';

    public function pool(bool $excludeSimilar = false): string
    {
        $pool = $this->value;

        if ($excludeSimilar) {
            $pool = str_replace(str_split(self::SIMILAR_CHARACTERS), '', $pool);
        }

        return $pool;
    }
}
