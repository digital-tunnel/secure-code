<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Tests;

use DigitalTunnel\SecureCode\Providers\SecureCodeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SecureCodeServiceProvider::class,
        ];
    }
}
