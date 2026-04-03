<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode;

use DigitalTunnel\SecureCode\Enums\Charset;
use Illuminate\Support\Facades\Cache;

final class CodeVault
{
    private int $length;

    private Charset $charset;

    private int $ttl;

    private int $maxAttempts;

    private string $prefix;

    public function __construct(
        int $length = 6,
        Charset $charset = Charset::Numeric,
        int $ttl = 300,
        int $maxAttempts = 5,
        string $prefix = 'secure_code_vault:',
    ) {
        $this->length = $length;
        $this->charset = $charset;
        $this->ttl = $ttl;
        $this->maxAttempts = $maxAttempts;
        $this->prefix = $prefix;
    }

    /**
     * Issue a new code for the given identifier and store it in cache.
     */
    public function issue(string $identifier): string
    {
        $code = (new CodeBuilder)
            ->length($this->length)
            ->charset($this->charset)
            ->generate();

        $key = $this->cacheKey($identifier);

        Cache::put($key, [
            'code' => $code,
            'attempts' => 0,
        ], $this->ttl);

        return $code;
    }

    /**
     * Verify a code against the stored value for the given identifier.
     * Returns true if valid, false otherwise. Automatically revokes on success.
     */
    public function verify(string $identifier, string $code): bool
    {
        $key = $this->cacheKey($identifier);
        $stored = Cache::get($key);

        if ($stored === null) {
            return false;
        }

        // Check max attempts
        if ($stored['attempts'] >= $this->maxAttempts) {
            Cache::forget($key);

            return false;
        }

        if (! hash_equals($stored['code'], $code)) {
            // Increment attempt counter
            $stored['attempts']++;
            Cache::put($key, $stored, $this->ttl);

            return false;
        }

        // Valid — revoke immediately
        Cache::forget($key);

        return true;
    }

    /**
     * Revoke (delete) the code for the given identifier.
     */
    public function revoke(string $identifier): void
    {
        Cache::forget($this->cacheKey($identifier));
    }

    /**
     * Check if a code exists (is pending) for the given identifier.
     */
    public function pending(string $identifier): bool
    {
        return Cache::has($this->cacheKey($identifier));
    }

    /**
     * Get the remaining attempts for the given identifier.
     */
    public function remainingAttempts(string $identifier): int
    {
        $stored = Cache::get($this->cacheKey($identifier));

        if ($stored === null) {
            return 0;
        }

        return max(0, $this->maxAttempts - $stored['attempts']);
    }

    private function cacheKey(string $identifier): string
    {
        return $this->prefix.hash('xxh128', $identifier);
    }
}
