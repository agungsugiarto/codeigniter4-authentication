<?php

namespace Fluent\Auth\Config;

use Config\Services as BaseService;
use Fluent\Auth\Authentication;
use Fluent\Auth\AuthenticationService;
use Fluent\Auth\Passwords;

class Services extends BaseService
{
    /**
     * The base auth class.
     *
     * @return AuthenticationService
     */
    public static function auth(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('auth');
        }

        $config = config('Auth');

        return new AuthenticationService(new Authentication($config));
    }

    /**
     * Password utilities.
     *
     * @return Passwords|mixed
     */
    public static function passwords(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('passwords');
        }

        return new Passwords(config('Auth'));
    }
}
