<?php

use Fluent\Auth\AuthenticationService;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Passwords;

if (! defined('auth')) {
    /**
     * Provides convenient access to the main Auth class
     * for CodeIgniter Shield.
     *
     * @return AuthenticationService|AuthenticationInterface
     */
    function auth(?string $authenticator = 'default')
    {
        return Services::auth()->withHandler($authenticator);
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
