<?php

namespace Fluent\Auth\Facades;

use CodeIgniter\HTTP\Response;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\UserProviderInterface;

/**
 * @see \Fluent\Auth\Contracts\AuthenticationInterface
 * @see \Fluent\Auth\AuthManager
 *
 * @method static $this guard($name = null)
 * @method static AuthenticationInterface factory(?string $adapter = 'default')
 * @method static AuthenticatorInterface authenticate()
 * @method static bool hasUser()
 * @method static bool check()
 * @method static int|null id()
 * @method static $this setUser(AuthenticatorInterface $user)
 * @method static UserProviderInterface getProvider()
 * @method static $this setRequest(RequestInterface $request)
 * @method static Response getResponse()
 * @method static bool attempt(array $credentials, bool $remember = false)
 * @method static bool validate(array $credentials)
 * @method static void login(AuthenticatorInterface $user, bool $remember = false)
 * @method static AuthenticatorInterface|bool loginById(int $userId, bool $remember = false)
 * @method static void logout()
 * @method static AuthenticatorInterface|HasAccessTokensInterface user()
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
        return Services::auth()->$method(...$arguments);
    }
}
