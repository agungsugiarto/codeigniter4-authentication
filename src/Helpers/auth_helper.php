<?php

use Fluent\Auth\AuthenticationService;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Passwords;

if (! defined('auth')) {
    /**
     * Provides convenient access to the main AuthenticationService class.
     *
     * @return AuthenticationService|AuthenticationInterface
     */
    function auth(?string $authenticator = 'default')
    {
        return Services::auth()->adapter($authenticator);
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
