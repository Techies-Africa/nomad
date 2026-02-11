<?php

namespace TechiesAfrica\Nomad\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneService;

class NomadMiddleware
{
    protected NomadTimezoneResolver $resolver;

    public function __construct(NomadTimezoneResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // After auth middleware has run, persist timezone if it changed
        $this->persistTimezone();

        return $response;
    }

    protected function persistTimezone(): void
    {
        $guard = Config::get('nomad.guard');
        $column = Config::get('nomad.column', 'timezone');

        try {
            $user = auth($guard)->user();
            if (!$user) {
                return;
            }

            $timezone = $this->resolver->getTimezone();

            if (($user->{$column} ?? null) !== $timezone) {
                (new NomadTimezoneService())
                    ->setUser($user->id)
                    ->setTimezone($timezone)
                    ->save();
            }
        } catch (\Throwable $th) {
            // Auth or DB not available — continue silently
        }
    }
}
