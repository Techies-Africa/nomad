<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Timezone;

use Illuminate\Support\Facades\DB;
use TechiesAfrica\Nomad\Observers\NomadTimezoneObserver;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;
use TechiesAfrica\Nomad\Tests\Stubs\Models\Post;
use TechiesAfrica\Nomad\Tests\TestCase;

class TimezoneConversionTest extends TestCase
{
    protected NomadTimezoneResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = $this->app->make(NomadTimezoneResolver::class);
        NomadTimezoneObserver::clearCache();
    }

    public function test_datetime_attributes_are_converted_to_user_timezone_on_read(): void
    {
        $utcTime = '2024-06-15 10:00:00';
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => $utcTime,
            'created_at' => $utcTime,
            'updated_at' => $utcTime,
        ]);

        // Africa/Lagos is UTC+1
        $this->resolver->setTimezone('Africa/Lagos');

        $post = Post::first();

        $this->assertEquals('Africa/Lagos', $post->created_at->getTimezone()->getName());
        $this->assertEquals(11, $post->created_at->hour);

        $this->assertEquals('Africa/Lagos', $post->published_at->getTimezone()->getName());
        $this->assertEquals(11, $post->published_at->hour);
    }

    public function test_no_conversion_when_timezone_is_utc(): void
    {
        $utcTime = '2024-06-15 10:00:00';
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => $utcTime,
            'created_at' => $utcTime,
            'updated_at' => $utcTime,
        ]);

        $this->resolver->setTimezone('UTC');

        $post = Post::first();
        $this->assertEquals(10, $post->created_at->hour);
    }

    public function test_excluded_models_are_not_converted(): void
    {
        config(['nomad.excluded_models' => [Post::class]]);
        NomadTimezoneObserver::clearCache();

        $utcTime = '2024-06-15 10:00:00';
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => $utcTime,
            'created_at' => $utcTime,
            'updated_at' => $utcTime,
        ]);

        $this->resolver->setTimezone('Africa/Lagos');

        $post = Post::first();
        $this->assertEquals(10, $post->created_at->hour);
    }

    public function test_non_datetime_attributes_are_unaffected(): void
    {
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'My Title',
            'published_at' => '2024-06-15 10:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->resolver->setTimezone('America/New_York');

        $post = Post::first();
        $this->assertEquals('My Title', $post->title);
    }

    public function test_null_datetime_attributes_remain_null(): void
    {
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => null,
            'created_at' => '2024-06-15 10:00:00',
            'updated_at' => '2024-06-15 10:00:00',
        ]);

        $this->resolver->setTimezone('Asia/Tokyo');

        $post = Post::first();
        $this->assertNull($post->published_at);
    }

    public function test_conversion_works_with_different_timezones(): void
    {
        $utcTime = '2024-06-15 10:00:00';
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => $utcTime,
            'created_at' => $utcTime,
            'updated_at' => $utcTime,
        ]);

        // Asia/Tokyo is UTC+9
        $this->resolver->setTimezone('Asia/Tokyo');

        $post = Post::first();
        $this->assertEquals(19, $post->created_at->hour);
        $this->assertEquals('Asia/Tokyo', $post->created_at->getTimezone()->getName());
    }

    public function test_conversion_works_with_negative_offset_timezones(): void
    {
        $utcTime = '2024-06-15 10:00:00';
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => $utcTime,
            'created_at' => $utcTime,
            'updated_at' => $utcTime,
        ]);

        // America/New_York is UTC-4 in June (EDT)
        $this->resolver->setTimezone('America/New_York');

        $post = Post::first();
        $this->assertEquals(6, $post->created_at->hour);
    }

    public function test_conversion_works_with_collection(): void
    {
        $userId = $this->createTestUser();
        foreach (range(1, 3) as $i) {
            DB::table('posts')->insert([
                'user_id' => $userId,
                'title' => "Post $i",
                'published_at' => "2024-06-15 1{$i}:00:00",
                'created_at' => '2024-06-15 10:00:00',
                'updated_at' => '2024-06-15 10:00:00',
            ]);
        }

        $this->resolver->setTimezone('Asia/Tokyo');

        $posts = Post::all();
        foreach ($posts as $post) {
            $this->assertEquals('Asia/Tokyo', $post->created_at->getTimezone()->getName());
        }
    }

    public function test_conversion_disabled_via_config(): void
    {
        config(['nomad.enabled' => false]);

        $utcTime = '2024-06-15 10:00:00';
        DB::table('posts')->insert([
            'user_id' => $this->createTestUser(),
            'title' => 'Test Post',
            'published_at' => $utcTime,
            'created_at' => $utcTime,
            'updated_at' => $utcTime,
        ]);

        $this->resolver->setTimezone('Africa/Lagos');

        $post = Post::first();
        $this->assertEquals(10, $post->created_at->hour);
    }

    protected function createTestUser(): int
    {
        return DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
