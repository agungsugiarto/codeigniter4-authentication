<?php

namespace Fluent\Auth;

use Closure;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Models\UserModel;
use FLuent\Auth\UserDatabase;
use InvalidArgumentException;

use function call_user_func;
use function count;
use function is_null;

class AuthManager implements AuthFactoryInterface
{
    /**
     * The config instance.
     *
     * @var Auth
     */
    protected $config;

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * The registered custom provider creators.
     *
     * @var array
     */
    protected $customProviderCreators = [];

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $guards = [];

    /**
     * The user resolver shared by various services.
     *
     * @var Closure
     */
    protected $userResolver;

    /**
     * Create a new Auth manager instance.
     *
     * @param Auth $config
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;

        $this->userResolver = function ($guard = null) {
            return $this->guard($guard)->user();
        };
    }

    /**
     * {@inheritdoc}
     */
    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver()
    {
        return $this->config->defaults['guard'];
    }

    /**
     * {@inheritdoc}
     */
    public function shouldUse($name)
    {
        $name = $name ?: $this->getDefaultDriver();

        $this->setDefaultDriver($name);

        $this->userResolver = function ($name = null) {
            return $this->guard($name)->user();
        };
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver($name)
    {
        $this->config->defaults['guard'] = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function userResolver()
    {
        return $this->userResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveUsersUsing(Closure $userResolver)
    {
        $this->userResolver = $userResolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createUserProvider($provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return;
        }

        if (isset($this->customProviderCreators[$driver = $config['driver']])) {
            return call_user_func(
                $this->customProviderCreators[$driver],
                $this->config,
                $config
            );
        }

        switch ($driver) {
            case 'model':
                return $this->createModelProvider($config);
            case 'connection':
                return $this->createDatabaseProvider($config);
            default:
                throw new InvalidArgumentException(
                    "Authentication user provider [{$driver}] is not defined."
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function provider($name, Closure $callback)
    {
        $this->customProviderCreators[$name] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultUserProvider()
    {
        return $this->config->defaults['provider'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasResolvedGuards()
    {
        return count($this->guards) > 0;
    }

    /**
     * Resolve the given guard.
     *
     * @param  string  $name
     * @return AuthenticationInterface
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($name, $config);
        }

        $driverMethod = new $config['driver']($this->config, $this->createUserProvider($config['provider']));

        if ($driverMethod instanceof AuthenticationInterface) {
            return $driverMethod;
        }

        throw new InvalidArgumentException(
            "Auth driver [{$config['driver']}] for guard [{$name}] must be instance of AuthenticationInterface."
        );
    }

    /**
     * Get the guard configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        if (isset($this->config->guards[$name])) {
            return $this->config->guards[$name];
        }

        throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
    }

    /**
     * Call a custom driver creator.
     *
     * @param  string  $name
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator($name, array $config)
    {
        return $this->customCreators[$config['driver']]($this->config, $name, $config);
    }

    /**
     * Get the user provider configuration.
     *
     * @param  string|null  $provider
     * @return array|null
     */
    protected function getProviderConfiguration($provider)
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->config->providers[$provider];
        }
    }

    /**
     * Create an instance of the database user provider.
     *
     * @param  array  $config
     * @return UserModel
     */
    protected function createModelProvider($config)
    {
        return new $config['table']();
    }

    /**
     * Create an instance of the Eloquent user provider.
     *
     * @param  array  $config
     * @return UserDatabase
     */
    protected function createDatabaseProvider($config)
    {
        return new $config['table']();
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
        return $this->guard()->{$method}(...$parameters);
    }
}
