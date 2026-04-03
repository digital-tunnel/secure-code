<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CodeBatchGenerated
{
    use Dispatchable;

    /**
     * @param  array<int, string>  $codes
     */
    public function __construct(
        public readonly array $codes,
        public readonly int $count,
    ) {}
}
