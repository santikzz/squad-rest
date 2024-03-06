<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\RateLimiter;

class RateLimitMiddleware
{

    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        $key = $request->ip();

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return response()->json(['message' => 'Too many attempts. Please try again later.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    protected function addHeaders($response, $maxAttempts, $remainingAttempts)
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }

    protected function calculateRemainingAttempts($key, $maxAttempts)
    {
        return max(0, $maxAttempts - $this->limiter->attempts($key) + 1);
    }
}
