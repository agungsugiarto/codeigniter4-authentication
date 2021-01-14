<?php

namespace Fluent\Auth;

use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

use function array_key_exists;

class AuthenticationFactory
{
    /**
     * Instantiated adapter objects,
     * stored by adapter alias.
     *
     * @var array
     */
    protected $instances = [];

    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var Auth */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Returns an instance of the specified adapter.
     *
     * You can pass 'default' as the adapter and it
     * will return an instance of the first adapter specified
     * in the Auth config file.
     *
     * @return AuthenticationInterface
     * @throws AuthenticationException
     */
    public function factory(string $adapter = 'default')
    {
        // Determine actual adapter name
        $adapter = $adapter === 'default'
            ? $this->config->defaults['adapter']
            : $adapter;

        // Otherwise, try to create a new instance.
        if (! array_key_exists($adapter, $this->config->adapters)) {
            throw AuthenticationException::forUnknownAdapter($adapter);
        }

        // Return the cached instance if we have it
        if (! empty($this->instances[$adapter])) {
            return $this->instances[$adapter];
        }

        // Class adapter implement AuthenticationInterface
        $classAdapter = $this->config->adapters[$adapter]['driver'];

        // Class user provider
        $this->userProvider = $this->config->adapters[$adapter]['provider'];

        // Instance authentication
        $this->instances[$adapter] = new $classAdapter($this->config, $this->userProvider());

        return $this->instances[$adapter];
    }

    /**
     * Set the name of the class that handles user persistence.
     *
     * @return UserProviderInterface
     */
    public function userProvider()
    {
        $adapter = new $this->userProvider();

        if (! $adapter instanceof UserProviderInterface) {
            throw AuthenticationException::forUnknownUserProvider();
        }

        return $adapter;
    }
}
