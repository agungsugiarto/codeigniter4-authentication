<?php

namespace Fluent\Auth\Config;

use CodeIgniter\Config\Services as BaseService;
use Fluent\Auth\AuthManager;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Contracts\HasherInterface;
use Fluent\Auth\Contracts\PasswordBrokerFactoryInterface;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Passwords\Hash\HashManager;
use Fluent\Auth\Passwords\Hash\Manager;
use Fluent\Auth\Passwords\PasswordBrokerManager;

class Services extends BaseService
{
    /**
     * The base auth class.
     *
     * @return AuthFactoryInterface|AuthenticationInterface
     */
    public static function auth(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('auth');
        }

        return new AuthManager(config('Auth'));
    }

    /**
     * Passwords broker services.
     *
     * @return PasswordBrokerFactoryInterface|PasswordBrokerInterface
     */
    public static function passwords(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('passwords');
        }

        return new PasswordBrokerManager(config('Auth'));
    }

    /**
     * Create HashManager instance.
     *
     * @return Manager|HashManager|HasherInterface
     */
    public static function hash(bool $getshared = true)
    {
        if ($getshared) {
            return self::getSharedInstance('hash');
        }

        return new HashManager(config('Hashing'));
    }
}
