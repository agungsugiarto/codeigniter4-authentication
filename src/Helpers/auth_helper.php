<?php

use Fluent\Auth\AuthManager;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Passwords;

if (! defined('auth')) {
    /**
     * Provides convenient access to the main authentication class.
     *
     * @return AuthManager|AuthenticationInterface
     */
    function auth($name = null)
    {
        return Services::auth()->guard($name);
    }
}

if (! defined('passwords')) {
    /**
     * Password utilities.
     *
     * @return Passwords|mixed
     */
    function passwords()
    {
        return Services::passwords();
    }
}
