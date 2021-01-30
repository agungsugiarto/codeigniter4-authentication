<?php

namespace Fluent\Auth\Config;

use CodeIgniter\Config\Services as BaseService;
use Fluent\Auth\AuthManager;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;

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
}
