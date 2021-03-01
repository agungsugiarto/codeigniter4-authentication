<?php

namespace Fluent\Auth\Passwords;

use CodeIgniter\Config\Factories;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\PasswordBrokerFactoryInterface;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Contracts\PasswordResetRepositoryInterface;
use Fluent\Auth\Passwords\PasswordBroker;
use InvalidArgumentException;

class PasswordBrokerManager implements PasswordBrokerFactoryInterface
{
    /** @var Auth */
    protected $config;

    /** @var array */
    protected $brokers = [];

    /**
     * Create a new PasswordBroker manager instance.
     *
     * @param bool getShared
     * @return void
     */
    public function __construct(Factories $factory, bool $getShared = true)
    {
        $this->config = $factory::config('Auth', ['getShared' => $getShared]);
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
     * @return PasswordResetRepositoryInterface
     */
    protected function createTokenRepository(array $config)
    {
        return new PasswordResetRepository($config['table'], $config['connection'], $config['expire'], $config['throttle']);
    }

    /**
     * Get the password broker configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        if (isset($this->config->passwords[$name])) {
            return $this->config->passwords[$name];
        }

        throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver()
    {
        return $this->config->defaults['password'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver($name)
    {
        $this->config->defaults['password'] = $name;

        return $this;
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
