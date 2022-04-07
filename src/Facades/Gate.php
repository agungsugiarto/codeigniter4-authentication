<?php

namespace Fluent\Auth\Facades;

use Fluent\Auth\Config\Services;

/**
 * @method static \Fluent\Auth\Authorization\Gate guessPolicyNamesUsing(callable $callback)
 * @method static mixed resolvePolicy(string $class)
 * @method static \Fluent\Auth\Authorization\Response authorize(string $ability, array|mixed $arguments = [])
 * @method static \Fluent\Auth\Authorization\Response inspect(string $ability, array|mixed $arguments = [])
 * @method static \Fluent\Auth\Authorization\Response allowIf(\Closure|bool $condition, string|null $message = null, mixed $code = null)
 * @method static \Fluent\Auth\Authorization\Response denyIf(\Closure|bool $condition, string|null $message = null, mixed $code = null)
 * @method static \Fluent\Auth\Contracts\GateInterface after(callable $callback)
 * @method static \Fluent\Auth\Contracts\GateInterface before(callable $callback)
 * @method static \Fluent\Auth\Contracts\GateInterface define(string $ability, callable|string $callback)
 * @method static \Fluent\Auth\Contracts\GateInterface resource(string $name, string $class, array $abilities = null)
 * @method static \Fluent\Auth\Contracts\GateInterface forUser(\Illuminate\Contracts\Auth\Authenticatable|mixed $user)
 * @method static \Fluent\Auth\Contracts\GateInterface policy(string $class, string $policy)
 * @method static array abilities()
 * @method static array policies()
 * @method static bool allows(string $ability, array|mixed $arguments = [])
 * @method static bool any(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool check(iterable|string $abilities, array|mixed $arguments = [])
 * @method static bool denies(string $ability, array|mixed $arguments = [])
 * @method static bool has(string $ability)
 * @method static mixed getPolicyFor(object|string $class)
 * @method static mixed raw(string $ability, array|mixed $arguments = [])
 *
 * @see \Fluent\Auth\Contracts\GateInterface
 */
class Gate
{
    /**
     * Get the registered name of the component.
     *
     * @param string $method
     * @param array $arguments
     * @return GateInterface
     */
    public static function __callStatic($method, $arguments)
    {
        return Services::getSharedInstance('gate')->{$method}(...$arguments);
    }
}
