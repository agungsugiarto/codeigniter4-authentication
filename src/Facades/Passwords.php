<?php

namespace Fluent\Auth\Facades;

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\PasswordBrokerFactoryInterface;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Contracts\PasswordResetRepositoryInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;

/**
 * @see \Fluent\Auth\Contracts\PasswordBrokerFactoryInterface
 * @see \Fluent\Auth\Contracts\PasswordBrokerInterface
 *
 * @method static PasswordBrokerInterface broker($name = null)
 * @method static string getDefaultDriver()
 * @method static $this setDefaultDriver($name)
 * @method static string sendResetLink(array $credentials, ?Closure $callback = null)
 * @method static string sendVerifyLink(array $credentials, ?Closure $callback = null)
 * @method static mixed reset(array $credentials, Closure $callback)
 * @method static ResetPasswordInterface|VerifyEmailInterface|null getUser(array $credentials)
 * @method static string createToken(ResetPasswordInterface $user)
 * @method static void deleteToken(ResetPasswordInterface $user)
 * @method static bool tokenExists(ResetPasswordInterface $user, $token)
 * @method static PasswordResetRepositoryInterface getRepository()
 */
class Passwords
{
    /**
     * Facade passwords instance service.
     *
     * @param string $method
     * @param array $arguments
     * @return PasswordBrokerFactoryInterface|PasswordBrokerInterface
     */
    public static function __callStatic($method, $arguments)
    {
        return Services::getSharedInstance('passwords')->{$method}(...$arguments);
    }
}
