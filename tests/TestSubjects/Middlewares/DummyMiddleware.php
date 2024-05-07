<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Middlewares;

use Closure;
use Illuminate\Http\Request;

class DummyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Dummy-Middleware', 'Applied');

        return $response;
    }
}
