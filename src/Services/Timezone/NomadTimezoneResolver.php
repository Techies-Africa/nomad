<?php

namespace TechiesAfrica\Nomad\Services\Timezone;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NomadTimezoneResolver
{
    protected ?string $cached = null;

    /**
     * Get the resolved timezone for the current request/user.
     */
    public function getTimezone(): string
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        return $this->cached = $this->resolve();
    }

    /**
     * Force a specific timezone (useful for testing or manual override).
     */
    public function setTimezone(string $timezone): void
    {
        if ($this->isValidTimezone($timezone)) {
            $this->cached = $timezone;
        }
    }

    /**
     * Clear the cached timezone.
     */
    public function clearCache(): void
    {
        $this->cached = null;
    }

    /**
     * Get the current request instance lazily from the container.
     * This ensures we always reference the current request, not a stale one.
     */
    protected function getRequest(): ?Request
    {
        try {
            return app('request');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve the timezone through the fallback chain.
     */
    protected function resolve(): string
    {
        $request = $this->getRequest();

        // 1. HTTP header (frontend sends user's detected timezone)
        $headerName = Config::get('nomad.header', 'X-Timezone');
        if ($request) {
            $headerTz = $request->header($headerName);
            if ($headerTz && $this->isValidTimezone($headerTz)) {
                $this->storeInSession($headerTz);
                return $headerTz;
            }
        }

        // 2. Session (persisted from a previous request)
        $sessionKey = Config::get('nomad.session_key', 'nomad_timezone');
        try {
            if ($request?->hasSession()) {
                $sessionTz = $request->session()->get($sessionKey);
                if ($sessionTz && $this->isValidTimezone($sessionTz)) {
                    return $sessionTz;
                }
            }
        } catch (\Throwable) {
            // Session not available
        }

        // 3. Authenticated user's stored timezone
        $guard = Config::get('nomad.guard');
        $column = Config::get('nomad.column', 'timezone');
        try {
            $user = auth($guard)->user();
            if ($user) {
                $userTz = $user->{$column} ?? null;
                if ($userTz && $this->isValidTimezone($userTz)) {
                    return $userTz;
                }
            }
        } catch (\Throwable) {
            // Auth not available (console, queue, etc.)
        }

        // 4. GeoIP fallback (optional, requires torann/geoip)
        if (Config::get('nomad.geoip.enabled', false) && $request && function_exists('geoip')) {
            try {
                $location = geoip($request->ip());
                $geoTz = $location->timezone ?? null;
                if ($geoTz && $this->isValidTimezone($geoTz)) {
                    $this->storeInSession($geoTz);
                    return $geoTz;
                }
            } catch (\Throwable) {
                // GeoIP service unavailable
            }
        }

        // 5. Config default_output_timezone
        $defaultTz = Config::get('nomad.default_output_timezone');
        if ($defaultTz && $this->isValidTimezone($defaultTz)) {
            return $defaultTz;
        }

        // 6. Application timezone (typically UTC)
        return config('app.timezone', 'UTC');
    }

    /**
     * Validate that a string is a recognized IANA timezone identifier.
     */
    public function isValidTimezone(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true)
            || $timezone === 'UTC';
    }

    /**
     * Store timezone in session for persistence across requests.
     */
    protected function storeInSession(string $timezone): void
    {
        $sessionKey = Config::get('nomad.session_key', 'nomad_timezone');
        try {
            $request = $this->getRequest();
            if ($request?->hasSession()) {
                $request->session()->put($sessionKey, $timezone);
            }
        } catch (\Throwable) {
            // Session not available
        }
    }
}
