<?php

namespace Fluent\Auth\Facades;

use Closure;
use Fluent\Auth\Config\Services;
use CodeIgniter\Router\RouteCollection;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;

/**
 * @see \Fluent\Auth\Contracts\AuthenticationInterface
 * @see \Fluent\Auth\Contracts\AuthFactoryInterface
 *
 * @method static UserProviderInterface createUserProvider($provider = null)
 * @method static string getDefaultUserProvider()
 * @method static AuthenticationInterface guard($name = null)
 * @method static string getDefaultDriver()
 * @method static $this setDefaultDriver($name)
 * @method static Closure userResolver()
 * @method static $this resolveUsersUsing(Closure $userResolver)
 * @method static $this extend($driver, Closure $callback)
 * @method static $this provider($name, Closure $callback)
 * @method static bool hasResolvedGuards()
 * @method static RouteCollection routes(array $options = [])
 * @method static AuthenticatorInterface|ResetPasswordInterface|VerifyEmailInterface|HasAccessTokensInterface authenticate()
 * @method static bool attempt(array $credentials, bool $remember = false)
 * @method static bool viaRemember()
 * @method static bool validate(array $credentials)
 * @method static bool check()
 * @method static mixed login(AuthenticatorInterface $user, bool $remember = false)
 * @method static AuthenticatorInterface|bool loginById(int $userId, bool $remember = false)
 * @method static void logout()
 * @method static AuthenticatorInterface|ResetPasswordInterface|VerifyEmailInterface|HasAccessTokensInterface|null user()
 * @method static int|null id()
 * @method static bool hasUser
 * @method static $this setUser(AuthenticatorInterface $user)
 * @method static string getSessionName()
 * @method static string getCookieName()
 * @method static UserProviderInterface getProvider()
 * @method static $this setProvider(UserProviderInterface $provider)
 */
class Auth
{
    /**
     * Facade auth instance service.
     *
     * @param string $method
     * @param array $arguments
     * @return AuthFactoryInterface|AuthenticationInterface
     */
    public static function __callStatic($method, $arguments)
    {
        return Services::getSharedInstance('auth')->{$method}(...$arguments);
    }
}
