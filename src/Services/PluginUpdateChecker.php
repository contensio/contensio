<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Services;

use Contensio\Support\Hook;
use Contensio\Support\PluginRegistry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PluginUpdateChecker
{
    /** Cache key for the updates result array. */
    const CACHE_KEY = 'contensio_plugin_updates';

    /** How long to cache results (seconds). 24 hours. */
    const CACHE_TTL = 86400;

    const USER_AGENT = 'Contensio/2.0';

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Return cached update info for all plugins.
     * Keyed by vendor/name. Each entry: { latest_version, download_url, changelog_url }.
     */
    public static function all(): array
    {
        return Cache::get(self::CACHE_KEY, []);
    }

    /**
     * Run the full update check and refresh the cache.
     * Returns the new results array.
     */
    public static function refresh(): array
    {
        $updates = self::fetch();
        Cache::put(self::CACHE_KEY, $updates, self::CACHE_TTL);
        return $updates;
    }

    /**
     * Return update info for a single plugin, or null if up-to-date / unmanaged.
     *
     * @return array{latest_version: string, download_url: string, changelog_url: string|null}|null
     */
    public static function forPlugin(string $name): ?array
    {
        return self::all()[$name] ?? null;
    }

    /**
     * True if a newer version is available for the given plugin.
     */
    public static function hasUpdate(string $name): bool
    {
        return isset(self::all()[$name]);
    }

    /**
     * Clear the cached results.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Internal ───────────────────────────────────────────────────────────────

    /**
     * Perform the full check against all update sources.
     */
    private static function fetch(): array
    {
        $updates = [];
        $plugins = PluginRegistry::all();
        $enabled = PluginRegistry::enabledNames();

        foreach ($plugins as $name => $plugin) {
            // Only check enabled plugins — reduces API calls and is what users care about
            if (! in_array($name, $enabled, true)) {
                continue;
            }

            $meta           = $plugin['meta'] ?? [];
            $currentVersion = ltrim($meta['version'] ?? '0.0.0', 'v');
            $repository     = $meta['repository'] ?? null;
            $updateUrl      = $meta['update_url'] ?? null;

            $info = null;

            if ($repository) {
                $info = self::checkGitHub($repository, $currentVersion);
            } elseif ($updateUrl) {
                $info = self::checkCustomUrl($updateUrl, $currentVersion);
            }

            if ($info) {
                $updates[$name] = $info;
            }
        }

        // Let plugins self-report update info (paid/license-gated plugins)
        $updates = Hook::applyFilters('contensio/plugin-update-info', $updates);

        return is_array($updates) ? $updates : [];
    }

    /**
     * Check GitHub releases API for a newer version.
     */
    private static function checkGitHub(string $repo, string $currentVersion): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get("https://api.github.com/repos/{$repo}/releases/latest");

            if (! $response->successful()) {
                return null;
            }

            $data    = $response->json();
            $tagName = $data['tag_name'] ?? null;

            if (! $tagName) {
                return null;
            }

            $latestVersion = ltrim($tagName, 'v');

            if (version_compare($latestVersion, $currentVersion, '<=')) {
                return null;
            }

            return [
                'latest_version' => $latestVersion,
                'download_url'   => "https://github.com/{$repo}/archive/refs/tags/{$tagName}.zip",
                'changelog_url'  => $data['html_url'] ?? "https://github.com/{$repo}/releases",
            ];
        } catch (\Throwable $e) {
            Log::debug("[PluginUpdateChecker] GitHub check failed for {$repo}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check a custom update endpoint for a newer version.
     * The endpoint must return: { version, download_url, changelog_url? }
     */
    private static function checkCustomUrl(string $url, string $currentVersion): ?array
    {
        if (! str_starts_with($url, 'https://')) {
            return null;
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->post($url, ['current_version' => $currentVersion]);

            if (! $response->successful()) {
                return null;
            }

            $data          = $response->json();
            $latestVersion = ltrim($data['version'] ?? '', 'v');
            $downloadUrl   = $data['download_url'] ?? null;

            if (! $latestVersion || ! $downloadUrl) {
                return null;
            }

            if (version_compare($latestVersion, $currentVersion, '<=')) {
                return null;
            }

            if (! str_starts_with($downloadUrl, 'https://')) {
                return null;
            }

            return [
                'latest_version' => $latestVersion,
                'download_url'   => $downloadUrl,
                'changelog_url'  => $data['changelog_url'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::debug("[PluginUpdateChecker] Custom URL check failed for {$url}: " . $e->getMessage());
            return null;
        }
    }
}
