<?php

namespace Matteoc99\LaravelPreference\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;

class PreferenceMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        /**@var Response $response * */
        $response = $next($request);
        if ($response->exception) {
            return match ($response?->exception::class) {
                PreferenceNotFoundException::class => response()->json([
                    'error' => $response->exception->getMessage()
                ], 404),
                default => $response,
            };
        }

        return $response;
    }
}