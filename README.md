<p align="center">
    <img src="art/logo.svg" width="400" alt="Secure Code">
</p>

<p align="center">
    <a href="https://github.com/digital-tunnel/secure-code/releases"><img src="https://img.shields.io/github/v/release/digital-tunnel/secure-code?style=flat-square" alt="Latest Version"></a>
    <a href="https://github.com/digital-tunnel/secure-code"><img src="https://img.shields.io/badge/php-%5E8.2-8892BF?style=flat-square" alt="PHP Version"></a>
    <a href="https://github.com/digital-tunnel/secure-code/blob/main/LICENSE"><img src="https://img.shields.io/github/license/digital-tunnel/secure-code?style=flat-square" alt="License"></a>
</p>

# Secure Code

**Cryptographically secure random code generator with a fluent API for Laravel.**

Generate PINs, voucher codes, serial keys, invite tokens, verification codes, and more -- all powered by PHP's `random_int()` CSPRNG under the hood.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Basic Generation](#basic-generation)
  - [Code Length](#code-length)
  - [Character Sets](#character-sets)
  - [Custom Character Pool](#custom-character-pool)
  - [Prefix & Suffix](#prefix--suffix)
  - [Separators](#separators)
  - [Case Forcing](#case-forcing)
  - [Exclude Similar Characters](#exclude-similar-characters)
  - [Batch Generation](#batch-generation)
  - [Uniqueness Checking](#uniqueness-checking)
  - [Database Uniqueness](#database-uniqueness)
  - [Max Attempts](#max-attempts)
  - [Presets](#presets)
  - [Pattern-Based Generation](#pattern-based-generation)
  - [Checksum (Luhn & Mod-97)](#checksum-luhn--mod-97)
  - [Code Masking](#code-masking)
  - [Entropy Calculator](#entropy-calculator)
  - [Code Vault (TTL & Verification)](#code-vault-ttl--verification)
  - [HashId Encoding](#hashid-encoding)
  - [Export (JSON, CSV, Text)](#export-json-csv-text)
  - [Events](#events)
  - [Validation Rule](#validation-rule)
  - [Artisan Command](#artisan-command)
  - [Blade Directive](#blade-directive)
  - [Facade](#facade)
  - [Combining Options](#combining-options)
- [API Reference](#api-reference)
- [Real-World Examples](#real-world-examples)
- [Architecture](#architecture)
- [Testing](#testing)
- [Security](#security)
- [License](#license)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | 11.x, 12.x, 13.x |
| ext-bcmath | * |

---

## Installation

```bash
composer require digitaltunnel/secure-code
```

The package auto-discovers its service provider and facade. No manual registration needed.

### Publish Configuration (optional)

```bash
php artisan vendor:publish --tag=secure-code-config
```

This publishes `config/secure-code.php` where you can set application-wide defaults.

---

## Quick Start

```php
use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Enums\Charset;

// Generate with defaults (8-char alphanumeric)
$code = SecureCode::generate(); // "a9Kf3mX2"

// 6-digit numeric PIN
$pin = SecureCode::length(6)->charset(Charset::Numeric)->generate(); // "847293"

// Use a preset
$voucher = SecureCode::preset('voucher')->generate(); // "A8K3-M7F2-B9X1-P4J6"

// Pattern-based
$code = SecureCode::pattern('AAA-999-AAA')->generate(); // "KFM-847-XBP"
```

---

## Configuration

After publishing, edit `config/secure-code.php`:

```php
return [
    'length' => 8,
    'charset' => 'Alphanumeric',
    'exclude_similar' => false,
    'max_attempts' => 1000,

    'vault' => [
        'ttl' => 300,           // seconds
        'max_attempts' => 5,
        'cache_prefix' => 'secure_code_vault:',
    ],

    'hashid' => [
        'salt' => '',
        'min_length' => 6,
    ],
];
```

All options can be overridden per-call via the fluent API.

---

## Usage

### Basic Generation

```php
use DigitalTunnel\SecureCode\SecureCode;

$code = SecureCode::generate();
// "a9Kf3mX2"  (8-char alphanumeric by default)
```

### Code Length

```php
SecureCode::length(4)->generate();   // "aK3m"
SecureCode::length(32)->generate();  // "a9Kf3mX2bP7nR4jL8wQ5vT6yU1cE0dG"
```

### Character Sets

The `Charset` enum provides 13 predefined character pools:

```php
use DigitalTunnel\SecureCode\Enums\Charset;

SecureCode::charset(Charset::Numeric)->generate();            // "84729361"
SecureCode::charset(Charset::Alpha)->generate();              // "aKfmXbPn"
SecureCode::charset(Charset::AlphaUpper)->generate();         // "AKFMXBPN"
SecureCode::charset(Charset::AlphaLower)->generate();         // "akfmxbpn"
SecureCode::charset(Charset::Alphanumeric)->generate();       // "a9Kf3mX2"
SecureCode::charset(Charset::AlphanumericUpper)->generate();  // "A9KF3MX2"
SecureCode::charset(Charset::AlphanumericLower)->generate();  // "a9kf3mx2"
SecureCode::charset(Charset::Hex)->generate();                // "3a7f1b9e"
SecureCode::charset(Charset::HexUpper)->generate();           // "3A7F1B9E"
SecureCode::charset(Charset::Binary)->generate();             // "10110010"
SecureCode::charset(Charset::Base32)->generate();             // "JBSWY3DP"
SecureCode::charset(Charset::Base58)->generate();             // "4K7nR2jL"
SecureCode::charset(Charset::Base64Safe)->generate();         // "aK3m-X_2b"
```

**Available Charsets:**

| Charset | Characters | Pool Size |
|---|---|---|
| `Numeric` | `0-9` | 10 |
| `Alpha` | `A-Z a-z` | 52 |
| `AlphaUpper` | `A-Z` | 26 |
| `AlphaLower` | `a-z` | 26 |
| `Alphanumeric` | `0-9 A-Z a-z` | 62 |
| `AlphanumericUpper` | `0-9 A-Z` | 36 |
| `AlphanumericLower` | `0-9 a-z` | 36 |
| `Hex` | `0-9 a-f` | 16 |
| `HexUpper` | `0-9 A-F` | 16 |
| `Binary` | `0 1` | 2 |
| `Base32` | `A-Z 2-7` | 32 |
| `Base58` | `1-9 A-H J-N P-Z a-k m-z` | 58 |
| `Base64Safe` | `A-Z a-z 0-9 - _` | 64 |

### Custom Character Pool

```php
SecureCode::pool('ABCDEF123456')->length(8)->generate();
// "B3A6D1F4"
```

### Prefix & Suffix

```php
SecureCode::length(8)->prefix('INV-')->generate();
// "INV-a9Kf3mX2"

SecureCode::length(8)->suffix('-2026')->generate();
// "a9Kf3mX2-2026"

SecureCode::length(6)->prefix('ORD-')->suffix('-US')->generate();
// "ORD-847293-US"
```

> Prefix and suffix are **not** counted toward `length`.

### Separators

```php
SecureCode::length(12)->separator('-', 4)->generate();
// "A8K3-M7F2-B9X1"

SecureCode::length(9)->separator(' ', 3)->generate();
// "847 293 615"
```

> Separators are inserted after generation and don't affect the random character count.

### Case Forcing

```php
SecureCode::length(10)->charset(Charset::Alpha)->uppercase()->generate();
// "AKFMXBPNRJ"

SecureCode::length(10)->charset(Charset::Alphanumeric)->lowercase()->generate();
// "a9kf3mx2bp"
```

### Exclude Similar Characters

Remove visually ambiguous characters (`0`, `O`, `1`, `I`, `l`) from the pool:

```php
SecureCode::charset(Charset::Alphanumeric)->excludeSimilar()->generate();
```

### Batch Generation

```php
$codes = SecureCode::length(8)->count(10)->generate();
// ['a9Kf3mX2', 'bP7nR4jL', ... ] (array of 10 strings)
```

When `count` is `1`, a single string is returned (not an array). Codes within a batch are always unique to each other.

### Uniqueness Checking

**Using a Closure:**

```php
$codes = SecureCode::length(10)
    ->count(50)
    ->unique(fn (string $code) => ! DB::table('vouchers')->where('code', $code)->exists())
    ->generate();
```

**Using the `UniquenessChecker` Interface:**

```php
use DigitalTunnel\SecureCode\Contracts\UniquenessChecker;

class VoucherUniquenessChecker implements UniquenessChecker
{
    public function isUnique(string $code): bool
    {
        return ! Voucher::where('code', $code)->exists();
    }
}

$codes = SecureCode::length(10)
    ->count(100)
    ->unique(new VoucherUniquenessChecker())
    ->generate();
```

### Database Uniqueness

Built-in shorthand for database uniqueness -- no manual closure needed:

```php
$code = SecureCode::length(10)
    ->uniqueInTable('vouchers', 'code')
    ->generate();

// With a specific database connection
$code = SecureCode::length(10)
    ->uniqueInTable('vouchers', 'code', 'mysql')
    ->generate();

// Batch of unique codes
$codes = SecureCode::length(10)
    ->count(500)
    ->uniqueInTable('promo_codes', 'code')
    ->generate();
```

### Max Attempts

```php
SecureCode::length(4)
    ->charset(Charset::Numeric)
    ->unique(fn ($code) => ! in_array($code, $existing))
    ->maxAttempts(5000)
    ->generate();
```

Default: `1000` (configurable in `config/secure-code.php`).

### Presets

Preconfigured templates for common use cases:

```php
use DigitalTunnel\SecureCode\Enums\Preset;

SecureCode::preset('pin')->generate();      // "847293"       (6 numeric digits)
SecureCode::preset('otp')->generate();      // "529184"       (6 numeric digits)
SecureCode::preset('voucher')->generate();  // "A8K3-M7F2-B9X1-P4J6" (16 upper, no similar, dashed)
SecureCode::preset('serial')->generate();   // "3A7F-1B9E-4C2D-8F5A-6E0B" (20 hex, dashed)
SecureCode::preset('api-key')->generate();  // "sk_aK3mX2bP7n..." (40 base64-safe, sk_ prefix)
SecureCode::preset('token')->generate();    // 64 alphanumeric chars
SecureCode::preset('invite')->generate();   // 12 base58 chars
```

You can also use the enum directly and override options:

```php
SecureCode::preset(Preset::Pin)->length(8)->generate();  // "84729361" (8-digit PIN)
```

**Available Presets:**

| Preset | Length | Charset | Extras |
|---|---|---|---|
| `pin` | 6 | Numeric | -- |
| `otp` | 6 | Numeric | -- |
| `voucher` | 16 | AlphanumericUpper | excludeSimilar, separator `-` every 4 |
| `serial` | 20 | HexUpper | separator `-` every 4 |
| `api-key` | 40 | Base64Safe | prefix `sk_` |
| `token` | 64 | Alphanumeric | -- |
| `invite` | 12 | Base58 | -- |

### Pattern-Based Generation

Define the exact shape of your code using placeholder characters:

| Placeholder | Produces |
|---|---|
| `A` | Uppercase letter (A-Z) |
| `a` | Lowercase letter (a-z) |
| `9` | Digit (0-9) |
| `X` | Hex character (0-9, A-F) |
| `*` | Any alphanumeric |
| Anything else | Kept as literal |

```php
SecureCode::pattern('AAA-999-AAA')->generate();   // "KFM-847-XBP"
SecureCode::pattern('99-AAAA-99')->generate();    // "84-KFMX-72"
SecureCode::pattern('INV-999-AA')->generate();    // "INV-847-KF"
SecureCode::pattern('XXXX-XXXX')->generate();     // "3A7F-1B9E"
SecureCode::pattern('***-***')->generate();        // "a9K-f3m"

// Batch from pattern
$codes = SecureCode::pattern('AAA-999')->count(100)->generate();
```

### Checksum (Luhn & Mod-97)

Append self-validating check digits to generated codes:

**Luhn (for numeric codes):**

```php
use DigitalTunnel\SecureCode\Support\Checksum;

$code = SecureCode::length(7)
    ->charset(Charset::Numeric)
    ->withChecksum('luhn')
    ->generate();
// "84729355" (7 digits + 1 Luhn check digit = 8 chars)

// Verify
SecureCode::verifyChecksum($code, 'luhn'); // true
Checksum::verifyLuhn($code);               // true
```

**Mod-97 (for alphanumeric codes):**

```php
$code = SecureCode::length(6)
    ->charset(Charset::AlphanumericUpper)
    ->withChecksum('mod97')
    ->generate();
// "A8K3MF42" (6 chars + 2 check digits = 8 chars)

SecureCode::verifyChecksum($code, 'mod97'); // true
```

**Use directly:**

```php
Checksum::appendLuhn('123456');    // "1234566"
Checksum::verifyLuhn('1234566');   // true
Checksum::appendMod97('ABC123');   // "ABC12374"
Checksum::verifyMod97('ABC12374'); // true
```

### Code Masking

Hide parts of a code for secure display in UIs and logs:

```php
SecureCode::mask('ABCD-EFGH-IJKL');
// "****-****-IJKL"  (last 4 visible, preserves dashes)

SecureCode::mask('ABCD-EFGH-IJKL', visibleEnd: 4, preserve: '-');
// "****-****-IJKL"

SecureCode::mask('ABCDEFGH', visibleStart: 3);
// "ABC*****"

SecureCode::mask('ABCDEFGHIJ', visibleStart: 2, visibleEnd: 2);
// "AB******IJ"

SecureCode::mask('sk_live_abc123def456', character: '#', visibleEnd: 6);
// "##############ef456"
```

### Entropy Calculator

Evaluate the security strength of your code configuration before generating:

```php
$info = SecureCode::length(8)->charset(Charset::Alphanumeric)->entropy();

// [
//     'bits'         => 47.63,
//     'strength'     => 'moderate',
//     'pool_size'    => 62,
//     'length'       => 8,
//     'combinations' => '218340105584896',
// ]
```

**Strength levels:**

| Bits | Strength |
|---|---|
| < 28 | very weak |
| 28-47 | weak |
| 48-79 | moderate |
| 80-127 | strong |
| 128+ | very strong |

### Code Vault (TTL & Verification)

Issue short-lived codes with automatic expiry and brute-force protection -- ideal for email verification, 2FA, and OTP flows:

```php
$vault = SecureCode::vault(
    length: 6,             // code length
    charset: Charset::Numeric,
    ttl: 300,              // expires in 5 minutes
    maxAttempts: 5,        // lock after 5 wrong guesses
);

// Issue a code
$code = $vault->issue('user@example.com');
// "847293" -- stored in cache, expires automatically

// Verify (returns true and auto-revokes on success)
$vault->verify('user@example.com', '847293'); // true
$vault->verify('user@example.com', '847293'); // false (already used)

// Check if a code is pending
$vault->pending('user@example.com'); // bool

// Remaining attempts before lockout
$vault->remainingAttempts('user@example.com'); // int

// Manually revoke
$vault->revoke('user@example.com');
```

The vault uses timing-safe comparison (`hash_equals`) and automatically deletes the code after max failed attempts to prevent brute-force attacks.

### HashId Encoding

Encode integer IDs into short, obfuscated strings (reversible):

```php
$hashid = SecureCode::hashid(salt: 'my-secret-salt', minLength: 8);

$encoded = $hashid->encode(12345);  // "X8kN3mBp"
$decoded = $hashid->decode($encoded); // 12345

// Different salts produce different encodings
SecureCode::hashid(salt: 'salt-a')->encode(42); // "aBcDeFgH"
SecureCode::hashid(salt: 'salt-b')->encode(42); // "xYzWvUtS"
```

### Export (JSON, CSV, Text)

Generate codes and export them directly:

```php
// JSON
$json = SecureCode::length(8)->count(100)->toJson();
// '["A8K3M7F2","B9X1P4J6",...]'

$json = SecureCode::length(8)->count(100)->toJson(pretty: true);
// Pretty-printed JSON

// CSV
$csv = SecureCode::preset('voucher')->count(50)->toCsv();
// "code\nA8K3-M7F2-B9X1-P4J6\n..."

$csv = SecureCode::length(8)->count(50)->toCsv('voucher_code');
// Custom header

// Plain text (one per line)
$text = SecureCode::length(8)->count(50)->toText();
// "A8K3M7F2\nB9X1P4J6\n..."
```

### Events

Opt-in event dispatching for audit logging:

```php
use DigitalTunnel\SecureCode\Events\CodeGenerated;
use DigitalTunnel\SecureCode\Events\CodeBatchGenerated;

// Enable events
$code = SecureCode::length(8)->withEvents()->generate();
// Dispatches CodeGenerated with $event->code

$codes = SecureCode::length(8)->count(10)->withEvents()->generate();
// Dispatches CodeBatchGenerated with $event->codes and $event->count
```

Listen for events in your `EventServiceProvider` or with closures:

```php
Event::listen(CodeGenerated::class, function (CodeGenerated $event) {
    Log::info('Code generated', ['code' => $event->code]);
});

Event::listen(CodeBatchGenerated::class, function (CodeBatchGenerated $event) {
    Log::info('Batch generated', ['count' => $event->count]);
});
```

Events are **not dispatched by default** -- you must call `withEvents()` to opt in.

### Validation Rule

Validate incoming codes against format, length, charset, pattern, or checksum:

```php
use DigitalTunnel\SecureCode\Rules\SecureCodeFormat;
use DigitalTunnel\SecureCode\Enums\Charset;

// Validate length
$request->validate([
    'code' => ['required', new SecureCodeFormat(length: 6)],
]);

// Validate charset
$request->validate([
    'code' => ['required', new SecureCodeFormat(charset: Charset::Numeric)],
]);

// Validate length + charset
$request->validate([
    'code' => ['required', new SecureCodeFormat(length: 8, charset: Charset::AlphanumericUpper)],
]);

// Validate against a pattern
$request->validate([
    'code' => ['required', new SecureCodeFormat(pattern: 'AAA-999-AAA')],
]);

// Validate with Luhn checksum
$request->validate([
    'code' => ['required', new SecureCodeFormat(verifyChecksum: true, checksumType: 'luhn')],
]);

// Validate with mod-97 checksum
$request->validate([
    'code' => ['required', new SecureCodeFormat(verifyChecksum: true, checksumType: 'mod97')],
]);
```

### Artisan Command

Generate codes from the command line:

```bash
# Default (8-char alphanumeric)
php artisan secure-code:generate

# Custom options
php artisan secure-code:generate --length=16 --charset=Numeric

# Batch
php artisan secure-code:generate --count=50

# With preset
php artisan secure-code:generate --preset=voucher

# With pattern
php artisan secure-code:generate --pattern="AAA-999-AAA"

# Formatted output
php artisan secure-code:generate --count=10 --json
php artisan secure-code:generate --count=10 --csv

# All options
php artisan secure-code:generate \
    --length=12 \
    --charset=AlphanumericUpper \
    --prefix=INV- \
    --suffix=-2026 \
    --separator=- \
    --every=4 \
    --upper \
    --exclude-similar \
    --checksum \
    --count=20
```

### Blade Directive

Quick inline generation in Blade templates:

```blade
<p>Your code: @securecode</p>
```

### Facade

The package registers a facade automatically:

```php
use DigitalTunnel\SecureCode\Facades\SecureCode;

SecureCode::length(8)->generate();
```

### Combining Options

All methods are chainable and can be freely combined:

```php
$vouchers = SecureCode::length(16)
    ->charset(Charset::AlphanumericUpper)
    ->excludeSimilar()
    ->separator('-', 4)
    ->prefix('GIFT-')
    ->suffix('-2026')
    ->withChecksum('mod97')
    ->withEvents()
    ->count(500)
    ->uniqueInTable('vouchers', 'code')
    ->maxAttempts(2000)
    ->generate();
```

---

## API Reference

### SecureCode (Static Entry Point)

| Method | Returns | Description |
|---|---|---|
| `generate()` | `string\|array` | Generate with default config |
| `length(int)` | `CodeBuilder` | Set code length |
| `charset(Charset)` | `CodeBuilder` | Set character set |
| `pool(string)` | `CodeBuilder` | Set custom character pool |
| `prefix(string)` | `CodeBuilder` | Set prefix |
| `suffix(string)` | `CodeBuilder` | Set suffix |
| `separator(string, int)` | `CodeBuilder` | Set separator and interval |
| `uppercase()` | `CodeBuilder` | Force uppercase |
| `lowercase()` | `CodeBuilder` | Force lowercase |
| `excludeSimilar(bool)` | `CodeBuilder` | Exclude ambiguous characters |
| `count(int)` | `CodeBuilder` | Set batch size |
| `unique(Closure\|UniquenessChecker)` | `CodeBuilder` | Set uniqueness checker |
| `uniqueInTable(string, string, ?string)` | `CodeBuilder` | Database uniqueness |
| `maxAttempts(int)` | `CodeBuilder` | Set max retry attempts |
| `preset(string\|Preset)` | `CodeBuilder` | Apply a preset |
| `pattern(string)` | `CodeBuilder` | Set generation pattern |
| `withChecksum(string)` | `CodeBuilder` | Append checksum digit |
| `withEvents(bool)` | `CodeBuilder` | Enable event dispatching |
| `mask(string, ...)` | `string` | Mask a code for display |
| `verifyChecksum(string, string)` | `bool` | Verify a checksum |
| `vault(int, Charset, int, int)` | `CodeVault` | Create a code vault |
| `hashid(string, int)` | `HashId` | Create a HashId encoder |

### CodeBuilder (Fluent Builder)

Immutable -- every method returns a **new** instance:

```php
$template = SecureCode::length(12)->charset(Charset::AlphanumericUpper)->separator('-', 4);

$code1 = $template->generate();                    // uses template
$code2 = $template->prefix('VIP-')->generate();    // extends template safely
```

Additional methods on CodeBuilder:

| Method | Returns | Description |
|---|---|---|
| `toJson(bool $pretty)` | `string` | Generate + export as JSON |
| `toCsv(string $header)` | `string` | Generate + export as CSV |
| `toText()` | `string` | Generate + export as text |
| `entropy()` | `array` | Calculate entropy info |

### Support Classes

| Class | Description |
|---|---|
| `Checksum::appendLuhn(string)` | Append Luhn check digit |
| `Checksum::verifyLuhn(string)` | Verify Luhn checksum |
| `Checksum::appendMod97(string)` | Append mod-97 check digits |
| `Checksum::verifyMod97(string)` | Verify mod-97 checksum |
| `Mask::apply(string, ...)` | Mask a code string |
| `Entropy::calculate(int, int)` | Calculate entropy bits |
| `Entropy::strength(float)` | Get strength label |
| `Export::toJson(array, bool)` | Export as JSON |
| `Export::toCsv(array, string)` | Export as CSV |
| `Export::toText(array)` | Export as plain text |
| `PatternGenerator::generate(string)` | Generate from pattern |
| `PatternGenerator::toRegex(string)` | Convert pattern to regex |
| `HashId::encode(int)` | Encode integer |
| `HashId::decode(string)` | Decode to integer |

---

## Real-World Examples

### Email Verification Flow

```php
$vault = SecureCode::vault(ttl: 600);  // 10 min expiry
$code = $vault->issue($user->email);
Mail::to($user)->send(new VerificationMail($code));

// Later, when user submits the code:
if ($vault->verify($user->email, $request->code)) {
    $user->markEmailAsVerified();
}
```

### Gift Card with Self-Validating Checksum

```php
$card = SecureCode::preset('voucher')
    ->withChecksum('mod97')
    ->uniqueInTable('gift_cards', 'code')
    ->generate();

// When redeeming:
if (! SecureCode::verifyChecksum($request->code, 'mod97')) {
    abort(422, 'Invalid gift card format.');
}
```

### Obfuscated Order URLs

```php
$hashid = SecureCode::hashid(salt: config('app.key'));

// Generate URL
$url = route('orders.show', $hashid->encode($order->id));
// /orders/X8kN3mBp

// Resolve in controller
$orderId = $hashid->decode($request->route('order'));
```

### Batch Promo Codes Export

```php
$csv = SecureCode::preset('voucher')
    ->count(10000)
    ->uniqueInTable('promo_codes', 'code')
    ->toCsv('promo_code');

Storage::put('exports/promo-codes.csv', $csv);
```

### Security Audit with Entropy Check

```php
$info = SecureCode::length(16)->charset(Charset::Base58)->entropy();

if ($info['bits'] < 80) {
    throw new \RuntimeException('Insufficient entropy for production tokens.');
}
```

### Two-Factor Authentication

```php
$vault = SecureCode::vault(length: 6, ttl: 120, maxAttempts: 3);
$code = $vault->issue($user->id);

// Display masked after generation
SecureCode::mask($code, visibleStart: 1, visibleEnd: 1);
// "8****3"
```

### Invoice Numbers

```php
$invoice = SecureCode::pattern('INV-9999-AAAA-99')
    ->generate();
// "INV-8472-KFMX-93"
```

---

## Architecture

```
src/
├── SecureCode.php              # Static entry point
├── CodeBuilder.php             # Immutable fluent builder
├── CodeGenerator.php           # CSPRNG engine (random_int)
├── CodeVault.php               # TTL-based issue/verify/revoke
├── Contracts/
│   └── UniquenessChecker.php   # Interface for uniqueness logic
├── Console/
│   └── GenerateCommand.php     # Artisan command
├── Enums/
│   ├── Charset.php             # 13 predefined character pools
│   └── Preset.php              # 7 preconfigured presets
├── Events/
│   ├── CodeGenerated.php       # Single code event
│   └── CodeBatchGenerated.php  # Batch event
├── Facades/
│   └── SecureCode.php          # Laravel Facade
├── Providers/
│   └── SecureCodeServiceProvider.php
├── Rules/
│   └── SecureCodeFormat.php    # Validation rule
└── Support/
    ├── Checksum.php            # Luhn & Mod-97
    ├── Entropy.php             # Entropy calculator
    ├── Export.php              # JSON, CSV, Text export
    ├── HashId.php              # Integer encoding/decoding
    ├── Mask.php                # Code masking
    └── PatternGenerator.php    # Pattern-based generation
```

**Security**: All randomness is produced by `random_int()`, which draws from the OS CSPRNG. The Code Vault uses `hash_equals` for timing-safe comparison.

**Immutability**: `CodeBuilder` clones itself on every fluent call. Safe to store and reuse as templates.

---

## Testing

The package ships with **138 Pest tests** covering every feature:

```bash
cd packages/digitaltunnel/secure-code
./vendor/bin/pest
```

```
  PASS  Tests\Unit\ChecksumTest .............. 10 tests
  PASS  Tests\Unit\CommandTest ............... 12 tests
  PASS  Tests\Unit\EntropyTest ................ 6 tests
  PASS  Tests\Unit\EventTest .................. 5 tests
  PASS  Tests\Unit\ExportTest ................. 8 tests
  PASS  Tests\Unit\HashIdTest ................. 9 tests
  PASS  Tests\Unit\MaskTest ................... 9 tests
  PASS  Tests\Unit\PatternTest ................ 9 tests
  PASS  Tests\Unit\PresetTest ................ 10 tests
  PASS  Tests\Unit\SecureCodeTest ............ 43 tests
  PASS  Tests\Unit\ValidationTest ............. 8 tests
  PASS  Tests\Unit\VaultTest ................. 10 tests

  Tests:    138 passed (332 assertions)
```

---

## Security

If you discover a security vulnerability, please send an email to **hey@digitaltunnel.net** instead of opening a public issue.

See [SECURITY.md](SECURITY.md) for full details on our security policy, supported versions, and best practices.

---

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
