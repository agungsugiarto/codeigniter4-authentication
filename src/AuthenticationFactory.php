<?php

namespace Fluent\Auth;

use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

use function array_key_exists;
use function key;

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
            ? key($this->config->authenticators)
            : $adapter;

        // Return the cached instance if we have it
        if (! empty($this->instances[$adapter])) {
            return $this->instances[$adapter];
        }

        // Otherwise, try to create a new instance.
        if (! array_key_exists($adapter, $this->config->authenticators)) {
            throw AuthenticationException::forUnknownAdapter($adapter);
        }

        $className = $this->config->authenticators[$adapter];

        $this->instances[$adapter] = new $className($this->config, $this->userProvider);

        return $this->instances[$adapter];
    }

    /**
     * Sets the User provider to use
     *
     * @return $this
     */
    public function setProvider(UserProviderInterface $provider)
    {
        $this->userProvider = $provider;

        return $this;
    }
}
