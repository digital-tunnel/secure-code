<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Sequence;

final class SequenceFormatter
{
    public static function format(
        string $template,
        int $sequence,
        int $padWidth = 5,
        string $prefix = '',
        string $suffix = '',
        string $separator = '-',
        ?\DateTimeInterface $date = null,
    ): string {
        $date ??= new \DateTimeImmutable;

        return str_replace(
            ['{prefix}', '{suffix}', '{sequence}', '{separator}', '{Y}', '{y}', '{m}', '{d}', '{timestamp}'],
            [
                $prefix,
                $suffix,
                str_pad((string) $sequence, $padWidth, '0', STR_PAD_LEFT),
                $separator,
                $date->format('Y'),
                $date->format('y'),
                $date->format('m'),
                $date->format('d'),
                (string) $date->getTimestamp(),
            ],
            $template,
        );
    }
}
