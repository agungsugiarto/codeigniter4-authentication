<?php

namespace Fluent\Auth\Contracts;

use Closure;

interface AuthFactoryInterface
{
    /**
     * Create the user provider implementation for the driver.
     *
     * @param  string|null  $provider
     * @return UserProviderInterface|null
     * @throws InvalidArgumentException
     */
    public function createUserProvider($provider = null);

    /**
     * Get the default user provider name.
     *
     * @return string
     */
    public function getDefaultUserProvider();

    /**
     * Attempt to get the guard from the local cache.
     *
     * @param  string|null  $name
     * @return AuthenticationInterface
     */
    public function guard($name = null);

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Set the default authentication driver name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setDefaultDriver($name);

    /**
     * Get the user resolver callback.
     *
     * @return Closure
     */
    public function userResolver();

    /**
     * Set the callback to be used to resolve users.
     *
     * @return $this
     */
    public function resolveUsersUsing(Closure $userResolver);

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @return $this
     */
    public function extend($driver, Closure $callback);

    /**
     * Register a custom provider creator Closure.
     *
     * @param  string  $name
     * @return $this
     */
    public function provider($name, Closure $callback);

    /**
     * Determines if any guards have already been resolved.
     *
     * @return bool
     */
    public function hasResolvedGuards();
}
