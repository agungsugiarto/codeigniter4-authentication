<?php

namespace Fluent\Auth\Passwords;

use Fluent\Auth\Config\Auth;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\PasswordBrokerFactoryInterface;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Contracts\PasswordResetInterface;
use Fluent\Auth\Passwords\PasswordBroker;
use InvalidArgumentException;

use function is_null;

class PasswordBrokerManager implements PasswordBrokerFactoryInterface
{
    /** @var Auth */
    protected $config;

    /** @var array */
    protected $brokers = [];

    /**
     * Create a new PasswordBroker manager instance.
     *
     * @param  Auth $config
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function broker($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->brokers[$name] ?? ($this->brokers[$name] = $this->resolve($name));
    }

    /**
     * Resolve the given broker.
     *
     * @param  string  $name
     * @return PasswordBrokerInterface
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }

        // The password broker uses a token repository to validate tokens and send user
        // password e-mails, as well as validating that password reset process as an
        // aggregate service of sorts providing a convenient interface for resets.
        return new PasswordBroker(
            $this->createTokenRepository($config),
            Services::auth()->createUserProvider($config['provider'])
        );
    }

    /**
     * Create a token repository instance based on the given configuration.
     *
     * @param  array  $config
     * @return PasswordResetInterface
     */
    protected function createTokenRepository(array $config)
    {
        return new $config['table']($config['expire'], $config['throttle']);
    }

    /**
     * Get the password broker configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config->passwords[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver()
    {
        return $this->config->defaults['passwords'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver($name)
    {
        return $this->config->defaults['passwords'] = $name;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->broker()->{$method}(...$parameters);
    }
}
