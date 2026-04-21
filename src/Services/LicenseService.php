<?php

/**
 * Contensio - The open content platform for Laravel.
 * License key validation service.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Services;

/**
 * Validates Contensio commercial license keys using Ed25519 signatures.
 *
 * Key format:  CLK1.<base64url(json_payload)>.<base64url(signature)>
 *
 * Payload structure:
 *   {
 *     "iss":      "contensio.com",
 *     "sub":      "example.com",          // domain the license was issued for
 *     "label":    "Company Name",         // human-readable label
 *     "features": ["whitelabel"],         // feature flags unlocked
 *     "exp":      1767225600,             // Unix timestamp (expiry)
 *     "iat":      1745064000              // Unix timestamp (issued at)
 *   }
 *
 * Generating a keypair (run once on contensio.com — keep secret key ONLY there):
 *   $pair   = sodium_crypto_sign_keypair();
 *   $public = base64_encode(sodium_crypto_sign_publickey($pair));  // → embed below
 *   $secret = base64_encode(sodium_crypto_sign_secretkey($pair));  // → store on contensio.com
 *
 * Generating a license key on contensio.com:
 *   $payload = base64url(json_encode(['iss'=>'contensio.com','sub'=>$domain, ...]));
 *   $msg     = 'CLK1.' . $payload;
 *   $sig     = base64url(sodium_crypto_sign_detached($msg, $secretKey));
 *   $key     = $msg . '.' . $sig;
 */
class LicenseService
{
    private const PREFIX = 'CLK1';

    /**
     * Ed25519 public keys used to verify license signatures (base64-encoded, 32 bytes).
     *
     * Multiple keys are supported for graceful key rotation. The verifier tries
     * each key in order until one succeeds. When rotating:
     *   1. Generate a new keypair on contensio.com
     *   2. Add the new public key as 'current' below
     *   3. Move the old key to a 'legacy_YYYYMMDD' slot
     *   4. Deploy CMS update - old and new licenses both verify
     *   5. Reissue old licenses at your own pace
     *   6. Eventually remove the legacy key
     *
     * The private keys stay ONLY on contensio.com - never commit them here.
     */
    private const PUBLIC_KEYS = [
        'current' => 'CONTENSIO_LICENSE_PUBLIC_KEY_PLACEHOLDER',
        // 'legacy_20260421' => 'old_key_here_after_rotation',
    ];

    /**
     * Parse and verify a license key.
     *
     * Returns an array with:
     *   'valid'   => bool
     *   'payload' => array|null  (decoded payload on success)
     *   'error'   => string|null (human-readable error on failure)
     */
    public static function parse(string $key): array
    {
        $key = trim($key);

        // ── 1. Format check ──────────────────────────────────────────────────
        $parts = explode('.', $key, 3);
        if (count($parts) !== 3) {
            return self::fail('Invalid license key format.');
        }

        [$prefix, $b64Payload, $b64Sig] = $parts;

        if ($prefix !== self::PREFIX) {
            return self::fail('Unrecognised license key version.');
        }

        // ── 2. Sodium availability ────────────────────────────────────────────
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            return self::fail('The PHP sodium extension is required to validate license keys. Enable ext-sodium.');
        }

        // ── 3. Decode the public keys ─────────────────────────────────────────
        $publicKeys = self::publicKeys();
        if (empty($publicKeys)) {
            return self::fail('License public key is not configured. Contact the Contensio administrator.');
        }

        // ── 4. Decode payload + signature ─────────────────────────────────────
        $rawPayload = self::b64UrlDecode($b64Payload);
        $rawSig     = self::b64UrlDecode($b64Sig);

        if ($rawPayload === false || $rawSig === false) {
            return self::fail('Malformed license key (base64 decode error).');
        }

        // ── 5. Verify Ed25519 signature (try all registered public keys) ─────
        $message = self::PREFIX . '.' . $b64Payload;
        $valid   = false;

        foreach ($publicKeys as $rawPublicKey) {
            try {
                if (sodium_crypto_sign_verify_detached($rawSig, $message, $rawPublicKey)) {
                    $valid = true;
                    break;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        if (! $valid) {
            return self::fail('License key signature is invalid. The key may have been tampered with.');
        }

        // ── 6. Decode payload JSON ────────────────────────────────────────────
        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            return self::fail('License key payload could not be decoded.');
        }

        // ── 7. Check issuer ───────────────────────────────────────────────────
        if (($payload['iss'] ?? '') !== 'contensio.com') {
            return self::fail('License key was not issued by contensio.com.');
        }

        // ── 8. Check expiry ───────────────────────────────────────────────────
        if (isset($payload['exp']) && time() > (int) $payload['exp']) {
            return self::fail('This license key has expired.');
        }

        // ── 9. Check domain binding ───────────────────────────────────────────
        $licenseDomain = self::normaliseDomain($payload['sub'] ?? '');
        $appDomain     = self::normaliseDomain(parse_url(config('app.url', ''), PHP_URL_HOST) ?? '');

        if (empty($licenseDomain)) {
            return self::fail('License key does not specify a domain (sub claim missing).');
        }

        if ($licenseDomain !== $appDomain) {
            return self::fail(
                "This license key was issued for \"{$licenseDomain}\" but this installation is running on \"{$appDomain}\". "
                . 'Purchase a license key for this domain at contensio.com.'
            );
        }

        return [
            'valid'   => true,
            'payload' => $payload,
            'error'   => null,
        ];
    }

    /**
     * Check if a given feature flag is present in a validated payload.
     */
    public static function hasFeature(array $payload, string $feature): bool
    {
        return in_array($feature, (array) ($payload['features'] ?? []), true);
    }

    /**
     * Return all valid decoded Ed25519 public key bytes (32 bytes each).
     *
     * @return string[]  Array of raw 32-byte public keys.
     */
    private static function publicKeys(): array
    {
        $keys = [];

        // Config override (single key, useful for testing).
        $configKey = config('contensio.license_public_key');
        if ($configKey && $configKey !== 'CONTENSIO_LICENSE_PUBLIC_KEY_PLACEHOLDER') {
            $raw = base64_decode($configKey, strict: true);
            if ($raw !== false && strlen($raw) === 32) {
                $keys[] = $raw;
            }
        }

        // Built-in keys from the constant (supports rotation).
        foreach (self::PUBLIC_KEYS as $b64) {
            if (empty($b64) || $b64 === 'CONTENSIO_LICENSE_PUBLIC_KEY_PLACEHOLDER') {
                continue;
            }
            $raw = base64_decode($b64, strict: true);
            if ($raw !== false && strlen($raw) === 32) {
                $keys[] = $raw;
            }
        }

        return $keys;
    }

    /**
     * Normalise a domain for comparison: lowercase, strip www., strip trailing dot.
     */
    private static function normaliseDomain(string $domain): string
    {
        $domain = strtolower(trim($domain, " \t\n\r\0\x0B."));
        return preg_replace('/^www\./', '', $domain) ?? $domain;
    }

    private static function b64UrlDecode(string $input): string|false
    {
        return base64_decode(strtr($input, '-_', '+/'), strict: false);
    }

    private static function fail(string $error): array
    {
        return ['valid' => false, 'payload' => null, 'error' => $error];
    }
}
