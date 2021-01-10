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
     * Instantiated handler objects,
     * stored by handler alias.
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
     * Returns an instance of the specified handler.
     *
     * You can pass 'default' as the handler and it
     * will return an instance of the first handler specified
     * in the Auth config file.
     *
     * @return AuthenticationInterface
     * @throws AuthenticationException
     */
    public function factory(string $handler = 'default')
    {
        // Determine actual handler name
        $handler = $handler === 'default'
            ? key($this->config->authenticators)
            : $handler;

        // Return the cached instance if we have it
        if (! empty($this->instances[$handler])) {
            return $this->instances[$handler];
        }

        // Otherwise, try to create a new instance.
        if (! array_key_exists($handler, $this->config->authenticators)) {
            throw AuthenticationException::forUnknownHandler($handler);
        }

        $className = $this->config->authenticators[$handler];

        $this->instances[$handler] = new $className($this->config, $this->userProvider);

        return $this->instances[$handler];
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
