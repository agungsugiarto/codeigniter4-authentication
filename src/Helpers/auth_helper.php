<?php

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;

if (! defined('auth')) {
    /**
     * Provides convenient access to the main authentication class.
     *
     * @param string|null $name
     * @return AuthFactoryInterface|AuthenticationInterface
     */
    function auth($name = null)
    {
        if (is_null($name)) {
            return Services::auth();
        }

        return Services::auth()->guard($name);
    }
}
