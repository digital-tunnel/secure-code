<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Rules;

use Closure;
use DigitalTunnel\SecureCode\Enums\Charset;
use DigitalTunnel\SecureCode\Support\Checksum;
use DigitalTunnel\SecureCode\Support\PatternGenerator;
use Illuminate\Contracts\Validation\ValidationRule;

class SecureCodeFormat implements ValidationRule
{
    private ?int $length;

    private ?Charset $charset;

    private ?string $pattern;

    private bool $verifyChecksum;

    private string $checksumType;

    public function __construct(
        ?int $length = null,
        ?Charset $charset = null,
        ?string $pattern = null,
        bool $verifyChecksum = false,
        string $checksumType = 'luhn',
    ) {
        $this->length = $length;
        $this->charset = $charset;
        $this->pattern = $pattern;
        $this->verifyChecksum = $verifyChecksum;
        $this->checksumType = $checksumType;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        // Pattern-based validation
        if ($this->pattern !== null) {
            $regex = PatternGenerator::toRegex($this->pattern);

            if (! preg_match($regex, $value)) {
                $fail("The :attribute does not match the expected pattern ({$this->pattern}).");
            }

            return;
        }

        // Length check
        if ($this->length !== null && strlen($value) !== $this->length) {
            $fail("The :attribute must be exactly {$this->length} characters.");

            return;
        }

        // Charset check
        if ($this->charset !== null) {
            $pool = $this->charset->value;
            $escapedPool = preg_quote($pool, '/');

            if (! preg_match('/^['.$escapedPool.']+$/', $value)) {
                $fail("The :attribute contains invalid characters for the {$this->charset->name} charset.");

                return;
            }
        }

        // Checksum verification
        if ($this->verifyChecksum) {
            $valid = match ($this->checksumType) {
                'luhn' => Checksum::verifyLuhn($value),
                'mod97' => Checksum::verifyMod97($value),
                default => true,
            };

            if (! $valid) {
                $fail('The :attribute has an invalid checksum.');
            }
        }
    }
}
