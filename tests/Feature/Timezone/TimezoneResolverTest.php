<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Timezone;

use Illuminate\Http\Request;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;
use TechiesAfrica\Nomad\Tests\TestCase;

class TimezoneResolverTest extends TestCase
{
    protected function makeResolverWithRequest(Request $request): NomadTimezoneResolver
    {
        $this->app->instance('request', $request);

        $resolver = new NomadTimezoneResolver();

        return $resolver;
    }

    public function test_resolves_from_http_header(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_TIMEZONE' => 'America/New_York',
        ]);

        $resolver = $this->makeResolverWithRequest($request);
        $this->assertEquals('America/New_York', $resolver->getTimezone());
    }

    public function test_ignores_invalid_header_timezone(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_TIMEZONE' => 'Not/A/Timezone',
        ]);

        $resolver = $this->makeResolverWithRequest($request);
        $this->assertEquals('UTC', $resolver->getTimezone());
    }

    public function test_resolves_from_session(): void
    {
        $request = Request::create('/', 'GET');
        $request->setLaravelSession(app('session.store'));
        $request->session()->put('nomad_timezone', 'Europe/Berlin');

        $resolver = $this->makeResolverWithRequest($request);
        $this->assertEquals('Europe/Berlin', $resolver->getTimezone());
    }

    public function test_resolves_from_config_default(): void
    {
        config(['nomad.default_output_timezone' => 'Asia/Tokyo']);

        $request = Request::create('/', 'GET');
        $resolver = $this->makeResolverWithRequest($request);
        $this->assertEquals('Asia/Tokyo', $resolver->getTimezone());
    }

    public function test_falls_back_to_utc(): void
    {
        $request = Request::create('/', 'GET');
        $resolver = $this->makeResolverWithRequest($request);
        $this->assertEquals('UTC', $resolver->getTimezone());
    }

    public function test_header_takes_priority_over_session(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_TIMEZONE' => 'America/Chicago',
        ]);
        $request->setLaravelSession(app('session.store'));
        $request->session()->put('nomad_timezone', 'Europe/London');

        $resolver = $this->makeResolverWithRequest($request);
        $this->assertEquals('America/Chicago', $resolver->getTimezone());
    }

    public function test_caches_resolved_timezone(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_TIMEZONE' => 'America/New_York',
        ]);

        $resolver = $this->makeResolverWithRequest($request);
        $first = $resolver->getTimezone();
        $second = $resolver->getTimezone();

        $this->assertEquals($first, $second);
    }

    public function test_set_timezone_overrides_resolution(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_TIMEZONE' => 'America/New_York',
        ]);

        $resolver = $this->makeResolverWithRequest($request);
        $resolver->setTimezone('Asia/Tokyo');
        $this->assertEquals('Asia/Tokyo', $resolver->getTimezone());
    }

    public function test_clear_cache_allows_re_resolution(): void
    {
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_X_TIMEZONE' => 'America/New_York',
        ]);

        $resolver = $this->makeResolverWithRequest($request);
        $resolver->setTimezone('Asia/Tokyo');
        $this->assertEquals('Asia/Tokyo', $resolver->getTimezone());

        $resolver->clearCache();
        // Now re-resolves from header
        $this->assertEquals('America/New_York', $resolver->getTimezone());
    }

    public function test_is_valid_timezone(): void
    {
        $resolver = new NomadTimezoneResolver();
        $this->assertTrue($resolver->isValidTimezone('America/New_York'));
        $this->assertTrue($resolver->isValidTimezone('UTC'));
        $this->assertTrue($resolver->isValidTimezone('Africa/Lagos'));
        $this->assertFalse($resolver->isValidTimezone('Invalid/Zone'));
        $this->assertFalse($resolver->isValidTimezone(''));
    }

    public function test_resolves_without_request(): void
    {
        // When no request is bound, should fall back to UTC
        $resolver = new NomadTimezoneResolver();
        $this->assertEquals('UTC', $resolver->getTimezone());
    }
}
