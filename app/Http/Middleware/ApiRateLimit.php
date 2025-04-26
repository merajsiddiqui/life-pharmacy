<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware for rate limiting API requests
 */
class ApiRateLimit
{
    /**
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * Create a new ApiRateLimit instance
     *
     * @param RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->ip();

        if ($this->limiter->tooManyAttempts($key, 60)) { // 60 requests per minute
            return response()->json([
                'status' => 'error',
                'message' => __('middleware.rate_limit.too_many_requests'),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key);

        $response = $next($request);

        $response->headers->add([
            'X-RateLimit-Limit' => 60,
            'X-RateLimit-Remaining' => $this->limiter->remaining($key, 60),
        ]);

        return $response;
    }
} 