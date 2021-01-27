<?php

namespace Fluent\Auth;

use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

use function array_key_exists;

class AuthenticationService
{
    /**
     * Instantiated adapter objects,
     * stored by adapter alias.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The adapter to use for this request.
     *
     * @var string
     */
    protected $adapter = 'default';

    /** @var Auth */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Sets the adapter that should be used for this request.
     *
     * @return $this
     */
    public function adapter(?string $adapter = 'default')
    {
        $this->adapter = $adapter;

        return $this;
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

        // Class user provider implement UserProviderInterface
        $userProvider = $this->config->adapters[$adapter]['provider'];

        // Instance authentication adapter
        $this->instances[$adapter] = new $classAdapter($this->config, new $userProvider());

        return $this->instances[$adapter];
    }

    /**
     * Dynamically call the default adapter instance.
     *
     * @param string $method
     * @param array $arguments
     * @return AuthenticationInterface
     */
    public function __call($method, $arguments)
    {
        return $this->factory($this->adapter)->{$method}(...$arguments);
    }
}
