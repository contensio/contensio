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

use Contensio\ContensioServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VersionChecker
{
    /** How long to cache the latest-release response (seconds). */
    const CACHE_TTL = 43200; // 12 hours

    const CACHE_KEY = 'contensio_latest_release';

    /**
     * Fetch the latest release from GitHub.
     *
     * Returns an array with keys: version, url, published_at
     * Returns null if the check is disabled, GitHub is unreachable, or rate-limited.
     */
    public static function latestRelease(): ?array
    {
        if (! config('contensio.version_check', true)) {
            return null;
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::fetchFromGitHub();
        });
    }

    /**
     * Returns true if a newer version is available than the currently installed one.
     */
    public static function updateAvailable(): bool
    {
        $latest = self::latestRelease();

        if (! $latest) {
            return false;
        }

        $current = ContensioServiceProvider::version();

        return version_compare($latest['version'], $current, '>');
    }

    /**
     * Returns the full update info array if an update is available, null otherwise.
     * Handy for passing directly to views.
     *
     * @return array{version: string, url: string, published_at: string}|null
     */
    public static function updateInfo(): ?array
    {
        if (! self::updateAvailable()) {
            return null;
        }

        return self::latestRelease();
    }

    /**
     * Clear the cached release data — useful after a manual update.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    private static function fetchFromGitHub(): ?array
    {
        $repo = config('contensio.github_repo', 'contensio/contensio');

        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'Contensio/' . ContensioServiceProvider::VERSION])
                ->get("https://api.github.com/repos/{$repo}/releases/latest");

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data['tag_name'])) {
                return null;
            }

            return [
                'version'      => ltrim($data['tag_name'], 'v'),
                'url'          => $data['html_url'] ?? "https://github.com/{$repo}/releases",
                'published_at' => $data['published_at'] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
