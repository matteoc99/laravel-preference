<?php

namespace Matteoc99\LaravelPreference\Http\Middlewares;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PreferenceMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        /**@var Response $response * */
        $response = $next($request);
        $e        = $response?->exception;
        if ($e) {
            return match ($e::class) {
                PreferenceNotFoundException::class => throw new NotFoundHttpException($e->getMessage(), $e),
                AuthorizationException::class => throw new HttpException(403, $e->getMessage(), $e),
                default => $response,
            };
        }

        return $response;
    }
}