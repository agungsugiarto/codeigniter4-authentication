<?php

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;

if (! function_exists('auth')) {
    /**
     * Provides convenient access to the main authentication class.
     *
     * @param string|null $guard
     * @return AuthFactoryInterface|AuthenticationInterface
     */
    function auth($guard = null)
    {
        if (is_null($guard)) {
            return Services::getSharedInstance('auth');
        }

        return Services::getSharedInstance('auth')->guard($guard);
    }
}

if (! function_exists('user_id')) {
    /**
     * Provide codeigniter4/authentitication-implementation.
     * Get the unique identifier for a current user.
     *
     * @param string|null $guard
     * @return string|int|null
     */
    function user_id($guard = null)
    {
        return auth($guard)->id();
    }
}

if (! function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}
