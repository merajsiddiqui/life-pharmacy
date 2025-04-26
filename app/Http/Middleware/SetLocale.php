<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language');
        
        // If no language header is provided or the language is not supported, use English
        if (!$locale || !in_array($locale, ['en', 'ar'])) {
            $locale = 'en';
        }

        App::setLocale($locale);
        
        return $next($request);
    }
} 