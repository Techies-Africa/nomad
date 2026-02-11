<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Timezone;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneService;
use TechiesAfrica\Nomad\Tests\TestCase;

class TimezoneServiceTest extends TestCase
{
    public function test_set_timezone(): void
    {
        $service = new NomadTimezoneService();
        $service->setTimezone('Africa/Lagos');

        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('timezone');
        $property->setAccessible(true);

        $this->assertEquals('Africa/Lagos', $property->getValue($service));
    }

    public function test_saves_timezone(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = (new NomadTimezoneService())
            ->setUser($userId)
            ->setTimezone('Africa/Lagos')
            ->save();

        $this->assertEquals(1, $result);
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'timezone' => 'Africa/Lagos',
        ]);
    }

    public function test_save_returns_false_without_user(): void
    {
        $result = (new NomadTimezoneService())
            ->setTimezone('Africa/Lagos')
            ->save();

        $this->assertFalse($result);
    }

    public function test_rejects_invalid_timezone(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        (new NomadTimezoneService())
            ->setUser($userId)
            ->setTimezone('Invalid/Timezone')
            ->save();
    }

    public function test_accepts_various_valid_timezones(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $timezones = ['America/New_York', 'Europe/London', 'Asia/Tokyo', 'UTC'];

        foreach ($timezones as $tz) {
            $result = (new NomadTimezoneService())
                ->setUser($userId)
                ->setTimezone($tz)
                ->save();

            $this->assertEquals(1, $result, "Failed to save timezone: $tz");
            $this->assertDatabaseHas('users', [
                'id' => $userId,
                'timezone' => $tz,
            ]);
        }
    }

    public function test_set_user_changes_target(): void
    {
        $userId1 = DB::table('users')->insertGetId([
            'name'  => 'User 1',
            'email' => 'user1@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId2 = DB::table('users')->insertGetId([
            'name'  => 'User 2',
            'email' => 'user2@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new NomadTimezoneService())
            ->setUser($userId1)
            ->setTimezone('Africa/Lagos')
            ->save();

        (new NomadTimezoneService())
            ->setUser($userId2)
            ->setTimezone('Asia/Tokyo')
            ->save();

        $this->assertDatabaseHas('users', ['id' => $userId1, 'timezone' => 'Africa/Lagos']);
        $this->assertDatabaseHas('users', ['id' => $userId2, 'timezone' => 'Asia/Tokyo']);
    }
}
