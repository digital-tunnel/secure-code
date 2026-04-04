<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Enums;

enum SequenceResetPeriod: string
{
    case Never = 'never';
    case Daily = 'daily';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function periodKey(\DateTimeInterface $date): string
    {
        return match ($this) {
            self::Never => '',
            self::Yearly => $date->format('Y'),
            self::Monthly => $date->format('Y-m'),
            self::Daily => $date->format('Y-m-d'),
        };
    }
}
