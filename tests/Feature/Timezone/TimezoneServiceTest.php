<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Timezone;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use TechiesAfrica\Nomad\Tests\TestCase;
use Mockery;
use TechiesAfrica\Nomad\Helper\StaticHelper;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneService;

class TimezoneServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close(); // Clean up Mockery
        parent::tearDown();
    }

    public function test_set_timezone()
    {
        $service = new NomadTimezoneService(1);
        $service->setTimezone('Africa/Lagos');
        $this->assertEquals('Africa/Lagos', $this->getProperty($service, 'timezone'));
    }

    public function test_saves_timezone()
    {
        $table_name = $this->getTableName();
        $this->ensureTableExists($table_name);
        $this->ensureFactoryExists($table_name);

        $user = $this->createUser($table_name);
        
        $timezone = 'Africa/Lagos';
        $service = new NomadTimezoneService($user->id);
        $service->setTimezone($timezone);

        $result = $service->save();

        $this->assertEquals(1, $result);
        $this->assertDatabaseHas($table_name, [
            'id' => $user->id,
            'timezone' => $timezone,
        ]);
    }

    /**
     * Get the configured table name.
     */
    protected function getTableName(): string
    {
        return Config::get("nomad.table", "users");
    }

    /**
     * Ensure the database table exists, creating it if necessary.
     */
    protected function ensureTableExists(string $table_name): void
    {
        if (!Schema::hasTable($table_name)) {
            Schema::create($table_name, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('timezone')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Ensure the factory class exists for the given table name.
     */
    protected function ensureFactoryExists(string $table_name): void
    {
        $model_class = StaticHelper::getModelFromTable($table_name);
        $model_name = StaticHelper::getModelName($table_name);

        if (!class_exists($model_class)) {
            $this->createDynamicModel($model_name, $table_name);
        }

        $this->createFactoryIfNotExists($model_name);
    }

    /**
     * Dynamically create a model class.
     */
    protected function createDynamicModel(string $model_name, string $table_name): void
    {
        eval("
            namespace App\Models;
            use Illuminate\Database\Eloquent\Model;
            use Illuminate\Database\Eloquent\Factories\HasFactory;

            class {$model_name} extends Model {
                use HasFactory;
                protected \$table = '{$table_name}';
            }
        ");
    }

    /**
     * Create a factory file for the given model if it does not exist.
     */
    protected function createFactoryIfNotExists(string $model_name): void
    {
        $factory_class_name = "{$model_name}Factory";
        $factory_path = database_path("factories/{$factory_class_name}.php");

        if (!is_dir(database_path('factories'))) {
            mkdir(database_path('factories'), 0755, true);
        }

        if (!class_exists("Database\Factories\\{$factory_class_name}") && !file_exists($factory_path)) {
            $factory_content = "<?php

            namespace Database\Factories;

            use App\Models\\{$model_name};
            use Illuminate\Database\Eloquent\Factories\Factory;

            class {$factory_class_name} extends Factory
            {
                protected \$model = \\App\Models\\{$model_name}::class;

                public function definition()
                {
                    return [
                        'name' => \$this->faker->name,
                        'email' => \$this->faker->unique()->safeEmail,
                    ];
                }
            }
            ";

            file_put_contents($factory_path, $factory_content);
        }

        require_once $factory_path;
    }

    /**
     * Create a user using the model's factory.
     */
    protected function createUser(string $table_name)
    {
        $model_class = StaticHelper::getModelFromTable($table_name);

        return $model_class::factory()->create();
    }

    /**
     * Helper method to access private/protected properties.
     */
    protected function getProperty($object, $property_name)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($property_name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
