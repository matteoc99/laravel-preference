<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Middlewares;
use Closure;
use Illuminate\Http\Request;

class Dummy2Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Dummy2-Middleware', 'Applied');

        return $response;
    }
}