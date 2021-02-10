<?php

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;

if (! defined('auth')) {
    /**
     * Provides convenient access to the main authentication class.
     *
     * @param string|null $guard
     * @return AuthFactoryInterface|AuthenticationInterface
     */
    function auth($guard = null)
    {
        if (is_null($guard)) {
            return Services::auth();
        }

        return Services::auth()->guard($guard);
    }
}
