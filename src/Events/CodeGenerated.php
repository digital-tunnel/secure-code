<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CodeGenerated
{
    use Dispatchable;

    public function __construct(
        public readonly string $code,
    ) {}
}
