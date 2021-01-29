<?php

namespace Fluent\Auth\Config;

use CodeIgniter\Config\Services as BaseService;
use Fluent\Auth\AuthenticationService;
use Fluent\Auth\AuthManager;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Passwords;

class Services extends BaseService
{
    /**
     * The base auth class.
     *
     * @return AuthenticationService|AuthenticationInterface
     */
    public static function auth(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('auth');
        }

        return new AuthManager(config('Auth'));
    }

    /**
     * Password utilities.
     *
     * @return Passwords
     */
    public static function passwords(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('passwords');
        }

        return new Passwords(config('Auth'));
    }
}
