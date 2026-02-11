# Nomad

Automatic UTC-to-user-timezone conversion for Laravel Eloquent models.

Nomad intercepts every Eloquent model as it loads from the database and converts all datetime attributes (`created_at`, `updated_at`, and any you define) from UTC to the current user's timezone. No traits, no per-model configuration. It works globally, out of the box.

## How It Works

1. Your database stores all datetimes in UTC (the Laravel default).
2. Nomad detects the user's timezone from: HTTP header, session, authenticated user's DB column, GeoIP, or config fallback.
3. When a model is retrieved, Nomad dynamically applies a custom Eloquent cast to every datetime column, converting UTC to the user's timezone.
4. When you save a model, the cast converts any timezone-aware Carbon instance back to UTC before writing to the database.

You read in local time. You write in UTC. Automatically.

## Requirements

- PHP >= 8.1
- Laravel 10.x, 11.x, or 12.x

## Installation

```bash
composer require techies-africa/nomad
```

Run the install command to publish the config file and migration:

```bash
php artisan nomad:install
```

Run the migration to add a `timezone` column to your users table:

```bash
php artisan migrate
```

### Register the Middleware

Add the Nomad middleware to your application. The middleware detects the user's timezone on each request and persists it to the database for authenticated users (only when the timezone changes).

**Laravel 11/12** (`bootstrap/app.php`):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\TechiesAfrica\Nomad\Middleware\NomadMiddleware::class);
})
```

**Laravel 10** (`app/Http/Kernel.php`):

```php
protected $middleware = [
    // ...
    \TechiesAfrica\Nomad\Middleware\NomadMiddleware::class,
];
```

If you need to customize the middleware, publish a local copy and reference that instead:

```bash
php artisan vendor:publish --tag=nomad-middleware
```

This publishes to `app/Http/Middleware/Nomad/NomadMiddleware.php`.

### Frontend: Sending the Timezone Header

For Nomad to detect the user's timezone, your frontend should send an `X-Timezone` header with every request. The browser's `Intl` API provides this:

```js
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
// "America/New_York", "Africa/Lagos", "Asia/Tokyo", etc.
```

**With Axios:**

```js
axios.defaults.headers.common['X-Timezone'] =
    Intl.DateTimeFormat().resolvedOptions().timeZone;
```

**With Fetch:**

```js
fetch('/api/endpoint', {
    headers: {
        'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone,
    },
});
```

## Usage

Once installed, Nomad works automatically. There is nothing to add to your models.

```php
// Database stores: 2024-06-15 10:00:00 (UTC)
// User's timezone: Africa/Lagos (UTC+1)

$post = Post::find(1);

$post->created_at;          // 2024-06-15 11:00:00 Africa/Lagos
$post->created_at->hour;    // 11
$post->created_at->timezone; // Africa/Lagos

// Saving converts back to UTC automatically
$post->published_at = now(); // Carbon instance in any timezone
$post->save();               // Stored as UTC in the database
```

### Excluding Models

If certain models should not have timezone conversion (e.g., audit logs that must stay in UTC):

```php
// config/nomad.php

'excluded_models' => [
    \App\Models\AuditLog::class,
    \App\Models\SystemEvent::class,
],
```

### Disabling Globally

To disable all timezone conversion without removing the package:

```php
// config/nomad.php

'enabled' => false,
```

## Timezone Detection

Nomad resolves the user's timezone through a fallback chain. The first valid result wins:

| Priority | Source | Description |
|----------|--------|-------------|
| 1 | HTTP Header | `X-Timezone` header sent by the frontend |
| 2 | Session | Timezone stored in session from a previous request |
| 3 | Database | Authenticated user's `timezone` column |
| 4 | GeoIP | IP-based lookup (optional, requires `torann/geoip`) |
| 5 | Config | `nomad.default_output_timezone` value |
| 6 | App timezone | `config('app.timezone')`, typically UTC |

### GeoIP Fallback (Optional)

To enable IP-based timezone detection as a fallback:

```bash
composer require torann/geoip
```

```php
// config/nomad.php

'geoip' => [
    'enabled' => true,
],
```

## Configuration

Publish the config file (done automatically by `nomad:install`):

```bash
php artisan vendor:publish --tag=nomad-config
```

Full config reference (`config/nomad.php`):

```php
return [
    // Master switch to enable/disable timezone conversion
    'enabled' => true,

    // Database table that stores user timezones
    'table' => 'users',

    // Column name for the timezone string
    'column' => 'timezone',

    // Auth guard (null = default guard)
    'guard' => null,

    // Models excluded from timezone conversion
    'excluded_models' => [],

    // Fallback timezone when no user timezone is available
    'default_output_timezone' => env('NOMAD_OUTPUT_TIMEZONE'),

    // HTTP header name for frontend timezone detection
    'header' => 'X-Timezone',

    // Session key for persisting detected timezone
    'session_key' => 'nomad_timezone',

    // GeoIP configuration
    'geoip' => [
        'enabled' => false,
    ],
];
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan nomad:install` | Publishes config and migration |
| `php artisan nomad:uninstall` | Removes published config, migration, and middleware files |
| `php artisan nomad:migrate` | Publishes and runs the timezone migration |

## Architecture

Nomad uses three core components:

**NomadDatetimeCast** - A Laravel custom cast (`CastsAttributes`). On `get()`: parses the raw UTC value and returns a Carbon instance in the user's timezone. On `set()`: converts any Carbon/DateTime instance back to a UTC string.

**NomadTimezoneObserver** - Listens to wildcard Eloquent events (`eloquent.retrieved: *` and `eloquent.created: *`). When any model is loaded, it discovers all datetime columns and dynamically applies `NomadDatetimeCast` via `$model->mergeCasts()`.

**NomadTimezoneResolver** - A request-scoped singleton that resolves the user's timezone through the fallback chain described above. The result is cached for the duration of the request.

This design means:
- No traits or interfaces to add to your models
- No dirty-state issues (raw database attributes are never modified by the observer)
- `isDirty()`, `toArray()`, `toJson()`, and `getOriginal()` all work correctly
- Standard Laravel pattern using documented Eloquent APIs

## Testing

```bash
composer test
```

## Uninstalling

```bash
php artisan nomad:uninstall
composer remove techies-africa/nomad
```

## Credits

- **Author**: [Joel Omojefe](https://www.linkedin.com/in/joel-omojefe/)
- **Organization**: [Techies Africa](https://techies.africa)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
