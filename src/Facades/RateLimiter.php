<?php

namespace Fluent\Auth\Facades;

use Closure;
use Fluent\Auth\Config\Services;

/**
 * @see \Fluent\Auth\Passwords\RateLimiter
 *
 * @method static \Fluent\Auth\Passwords\RateLimiter for(string $name, Closure $callback)
 * @method static Closure limiter(string $name)
 * @method static bool tooManyAttempts($key, $maxAttempts)
 * @method static int hit($key, $decaySeconds = 60)
 * @method static mixed attempts($key)
 * @method static mixed resetAttempts($key)
 * @method static int retriesLeft($key, $maxAttempts)
 * @method static void clear($key)
 * @method static int availableIn($key)
 */
class RateLimiter
{
    /**
     * Facade rate limiter instance service.
     *
     * @param string $method
     * @param array $arguments
     * @return \Fluent\Auth\Passwords\RateLimiter
     */
    public static function __callStatic($method, $arguments)
    {
        return Services::getSharedInstance('limiter')->{$method}(...$arguments);
    }
}
