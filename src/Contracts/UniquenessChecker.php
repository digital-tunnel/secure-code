<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Contracts;

interface UniquenessChecker
{
    /**
     * Return true if the given code is unique (i.e. does not already exist).
     */
    public function isUnique(string $code): bool;
}
