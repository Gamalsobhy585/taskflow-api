<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Lang
{
    private const SUPPORTED_LOCALES = [
        'en',
        'ar',
    ];

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $locale = $request->getPreferredLanguage(
            self::SUPPORTED_LOCALES
        );

        app()->setLocale(
            $locale ?: config('app.fallback_locale', 'en')
        );

        return $next($request);
    }
}