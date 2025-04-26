<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CustomSanctumAuthentication extends EnsureFrontendRequestsAreStateful
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->bearerToken()) {
            return new JsonResponse([
                'status' => false,
                'message' => __('auth.messages.unauthenticated'),
                'data' => null
            ], 401);
        }

        return parent::handle($request, $next);
    }
} 