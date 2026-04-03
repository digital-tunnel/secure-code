<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Enums;

enum Preset: string
{
    case Pin = 'pin';
    case Otp = 'otp';
    case Voucher = 'voucher';
    case Serial = 'serial';
    case ApiKey = 'api-key';
    case Token = 'token';
    case Invite = 'invite';

    /**
     * @return array{length: int, charset: Charset, separator?: string, separator_every?: int, prefix?: string, exclude_similar?: bool, uppercase?: bool, lowercase?: bool}
     */
    public function config(): array
    {
        return match ($this) {
            self::Pin, self::Otp => [
                'length' => 6,
                'charset' => Charset::Numeric,
            ],
            self::Voucher => [
                'length' => 16,
                'charset' => Charset::AlphanumericUpper,
                'exclude_similar' => true,
                'separator' => '-',
                'separator_every' => 4,
            ],
            self::Serial => [
                'length' => 20,
                'charset' => Charset::HexUpper,
                'separator' => '-',
                'separator_every' => 4,
            ],
            self::ApiKey => [
                'length' => 40,
                'charset' => Charset::Base64Safe,
                'prefix' => 'sk_',
            ],
            self::Token => [
                'length' => 64,
                'charset' => Charset::Alphanumeric,
            ],
            self::Invite => [
                'length' => 12,
                'charset' => Charset::Base58,
            ],
        };
    }
}
