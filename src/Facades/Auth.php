<?php

namespace Fluent\Auth\Facades;

use Fluent\Auth\AuthenticationFactory;
use Fluent\Auth\AuthenticationService;
use Fluent\Auth\Config\Auth as ConfigAuth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Result;

/**
 * @see \Fluent\Auth\Contracts\AuthenticationInterface
 * @see \Fluent\Auth\AuthenticationFactory
 * @see \Fluent\Auth\AuthenticationService
 *
 * @method static $this adapter(?string $adapter = 'default')
 * @method static AuthenticatorInterface|User|null user()
 * @method static int|null id()
 * @method static UserProviderInterface getProvider()
 * @method static AuthenticationInterface factory(string $adapter = 'default')
 * @method static $this setProvider(UserProviderInterface $provider)
 * @method static Result attempt(array $credentials, bool $remember = false)
 * @method static Result check(array $credentials)
 * @method static bool loggedIn()
 * @method static bool login(AuthenticatorInterface $user, bool $remember = false)
 * @method static bool loginById(int $userId, bool $remember = false)
 * @method static null logout()
 * @method static mixed forget(?int $id)
 * @method static AuthenticatorInterface|User|null getUser()
 */
class Auth
{
    /**
     * Facade auth instance authentication service.
     *
     * @param string $method
     * @param array $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        return (new AuthenticationService(new AuthenticationFactory(new ConfigAuth())))->$method(...$arguments);
    }
}
