<?php

declare(strict_types=1);

it('generates a default code via artisan command', function () {
    $this->artisan('secure-code:generate')
        ->assertSuccessful();
});

it('generates a code with custom length', function () {
    $this->artisan('secure-code:generate', ['--length' => 12])
        ->assertSuccessful();
});

it('generates a code with numeric charset', function () {
    $this->artisan('secure-code:generate', ['--charset' => 'Numeric', '--length' => 6])
        ->assertSuccessful();
});

it('generates multiple codes', function () {
    $this->artisan('secure-code:generate', ['--count' => 5])
        ->assertSuccessful();
});

it('generates codes with a preset', function () {
    $this->artisan('secure-code:generate', ['--preset' => 'voucher'])
        ->assertSuccessful();
});

it('generates codes from a pattern', function () {
    $this->artisan('secure-code:generate', ['--pattern' => 'AAA-999'])
        ->assertSuccessful();
});

it('outputs as JSON', function () {
    $this->artisan('secure-code:generate', ['--json' => true, '--count' => 3])
        ->assertSuccessful();
});

it('outputs as CSV', function () {
    $this->artisan('secure-code:generate', ['--csv' => true, '--count' => 3])
        ->assertSuccessful();
});

it('generates with prefix and suffix', function () {
    $this->artisan('secure-code:generate', ['--prefix' => 'PRE-', '--suffix' => '-END'])
        ->assertSuccessful();
});

it('generates with separator', function () {
    $this->artisan('secure-code:generate', ['--separator' => '-', '--every' => 4, '--length' => 12])
        ->assertSuccessful();
});

it('generates uppercase codes', function () {
    $this->artisan('secure-code:generate', ['--upper' => true])
        ->assertSuccessful();
});

it('generates with exclude-similar', function () {
    $this->artisan('secure-code:generate', ['--exclude-similar' => true])
        ->assertSuccessful();
});
