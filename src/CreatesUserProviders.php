<?php

namespace Fluent\Auth;

use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\DatabaseUserProvider;
use InvalidArgumentException;

use function call_user_func;
use function is_null;

trait CreatesUserProviders
{
    /**
     * The registered custom provider creators.
     *
     * @var array
     */
    protected $customProviderCreators = [];

    /**
     * Create the user provider implementation for the driver.
     *
     * @param  string|null  $provider
     * @return UserProviderInterface|null
     * @throws InvalidArgumentException
     */
    public function createUserProvider($provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return;
        }

        if (isset($this->customProviderCreators[$driver = $config['driver'] ?? null])) {
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
     * @return DatabaseUserProvider
     */
    protected function createDatabaseProvider($config)
    {
        return new DatabaseUserProvider($config['table']);
    }

    /**
     * Create an instance of the Eloquent user provider.
     *
     * @param  array  $config
     * @return EloquentUserProvider
     */
    protected function createModelProvider($config)
    {
        return new $config['table']();
    }

    /**
     * Get the default user provider name.
     *
     * @return string
     */
    public function getDefaultUserProvider()
    {
        return $this->config->defaults['provider'];
    }
}
