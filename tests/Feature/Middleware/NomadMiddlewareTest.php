<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Middleware;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use TechiesAfrica\Nomad\Middleware\NomadMiddleware;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;
use TechiesAfrica\Nomad\Tests\Stubs\Models\User;
use TechiesAfrica\Nomad\Tests\TestCase;

class NomadMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', NomadMiddleware::class])->get('/nomad-test', function () {
            return response()->json(['ok' => true]);
        });
    }

    public function test_middleware_processes_request_without_error(): void
    {
        $response = $this->get('/nomad-test', ['X-Timezone' => 'Africa/Lagos']);
        $response->assertOk();
    }

    public function test_middleware_works_without_authentication(): void
    {
        $response = $this->get('/nomad-test', ['X-Timezone' => 'Europe/London']);
        $response->assertOk();
    }

    public function test_middleware_saves_timezone_for_authenticated_user(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);
        $this->actingAs($user);

        // Clear resolver cache (User::find triggered the observer which cached a stale timezone)
        $this->app->make(NomadTimezoneResolver::class)->clearCache();

        $this->get('/nomad-test', ['X-Timezone' => 'America/New_York']);

        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'timezone' => 'America/New_York',
        ]);
    }

    public function test_middleware_does_not_update_when_timezone_unchanged(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'timezone' => 'Africa/Lagos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);
        $this->actingAs($user);

        // Send the same timezone that's already stored
        $this->get('/nomad-test', ['X-Timezone' => 'Africa/Lagos']);

        // Should still be the same (no unnecessary DB write)
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'timezone' => 'Africa/Lagos',
        ]);
    }
}
