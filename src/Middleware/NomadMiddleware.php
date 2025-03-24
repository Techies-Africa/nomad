<?php

namespace TechiesAfrica\Nomad\Middleware;

use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneService;
use Closure;
use Illuminate\Http\Request;

class NomadMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $timezone = geoip($request->ip())->timezone ?? $request->header("timezone");  
        (new NomadTimezoneService)
            ->setTimezone($timezone)
            ->save();

        return $next($request);
    }
}
