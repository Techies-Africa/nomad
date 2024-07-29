<?php

namespace TechiesAfrica\Nomad\Http\Middleware;

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
        (new NomadTimezoneService)
            ->setTimezone($request->header("timezone"))
            ->save();

        return $next($request);
    }
}
