# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability within Secure Code, please report it responsibly.

**Do not open a public GitHub issue.**

Instead, send an email to:

**hey@digitaltunnel.net**

Please include:

- A description of the vulnerability
- Steps to reproduce the issue
- The potential impact
- Any suggested fix (optional)

We will acknowledge your report within **48 hours** and aim to release a patch within **7 days** of confirmation.

## Supported Versions

| Version | Supported |
|---|---|
| 1.x | Yes |

## Security Best Practices

When using this package:

- **Never log or expose generated codes** in plain text for sensitive use cases (OTPs, tokens, API keys)
- **Use the Code Vault** for OTP/verification flows -- it handles TTL expiry, brute-force protection, and timing-safe comparison automatically
- **Use `excludeSimilar()`** for human-readable codes to prevent confusion attacks (e.g., `0` vs `O`)
- **Use `withChecksum()`** for codes that need self-validation (gift cards, serial keys) to catch typos before database lookup
- **Use `uniqueInTable()`** or the `UniquenessChecker` interface to prevent collisions in production
- **Check entropy** before deploying: `SecureCode::length(n)->charset($c)->entropy()` -- aim for 80+ bits for security-sensitive tokens
- **Use the `SecureCodeFormat` validation rule** to reject malformed codes at the input boundary

## Cryptographic Guarantees

All randomness in this package is produced by PHP's `random_int()`, which sources entropy from the operating system's CSPRNG:

- Linux/macOS: `/dev/urandom` via `getrandom(2)`
- Windows: `CryptGenRandom` / BCryptGenRandom

The Code Vault uses `hash_equals()` for timing-safe comparison to prevent timing attacks on verification endpoints.
