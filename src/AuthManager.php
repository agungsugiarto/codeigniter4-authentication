<?php

namespace Fluent\Auth;

use Closure;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use Fluent\Auth\Adapters\SessionAdapter;
use Fluent\Auth\Adapters\TokenAdapter;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\UserDatabase;
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
     * @param bool getShared
     * @return void
     */
    public function __construct(Factories $factory, bool $getShared = true)
    {
        $this->config = $factory::config('Auth', ['getShared' => $getShared]);

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
    public function resolveUsersUsing(Closure $callback)
    {
        $this->userResolver = $callback;

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
                $this,
                $config
            );
        }

        switch ($driver) {
            case 'model':
                return new $config['table']();
            case 'connection':
                return new UserDatabase($config['table'], $config['connection']);
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
     * {@inheritdoc}
     */
    public function routes(array $options = [])
    {
        $routes = Services::routes();

        if ($options['register'] ?? true) {
            $routes->get('register', 'RegisteredUserController::new', ['filter' => 'guest', 'namespace' => 'App\Controllers\Auth']);
            $routes->post('register', 'RegisteredUserController::create', ['filter' => 'guest', 'namespace' => 'App\Controllers\Auth']);
        }

        if ($options['reset'] ?? true) {
            $routes->get('forgot-password', 'PasswordResetLinkController::new', ['filter' => 'guest', 'as' => 'password.request', 'namespace' => 'App\Controllers\Auth']);
            $routes->post('forgot-password', 'PasswordResetLinkController::create', ['filter' => 'guest', 'as' => 'password.email', 'namespace' => 'App\Controllers\Auth']);
            $routes->get('reset-password/(:any)', 'NewPasswordController::new/$1', ['filter' => 'guest', 'as' => 'password.reset', 'namespace' => 'App\Controllers\Auth']);
            $routes->post('reset-password', 'NewPasswordController::create', ['filter' => 'guest', 'as' => 'password.update', 'namespace' => 'App\Controllers\Auth']);
        }

        if ($options['confirm'] ?? true) {
            $routes->get('confirm-password', 'ConfirmablePasswordController::show', ['filter' => 'auth', 'as' => 'password.confirm', 'namespace' => 'App\Controllers\Auth']);
            $routes->post('confirm-password', 'ConfirmablePasswordController::create', ['filter' => 'auth', 'namespace' => 'App\Controllers\Auth']);
        }

        if ($options['verify'] ?? true) {
            $routes->group('verify-email', ['filter' => 'auth', 'namespace' => 'App\Controllers\Auth'], function ($routes) {
                $routes->get('/', 'EmailVerificationPromptController::new', ['as' => 'verification.notice', 'namespace' => 'App\Controllers\Auth']);
                $routes->get('(:any)', 'VerifyEmailController::index/$1', ['filter' => 'throttle:60,5', 'as' => 'verification.verify', 'namespace' => 'App\Controllers\Auth']);
            });

            $routes->group('email', ['filter' => 'auth', 'namespace' => 'App\Controllers\Auth'], function ($routes) {
                $routes->post('verification-notification', 'EmailVerificationNotificationController::create', ['filter' => 'throttle:60,5', 'as' => 'verification.send']);
            });
        }

        $routes->get('login', 'AuthenticatedSessionController::new', ['filter' => 'guest', 'namespace' => 'App\Controllers\Auth']);
        $routes->post('login', 'AuthenticatedSessionController::create', ['filter' => 'guest', 'namespace' => 'App\Controllers\Auth']);
        $routes->post('logout', 'AuthenticatedSessionController::delete', ['filter' => 'auth', 'as' => 'logout', 'namespace' => 'App\Controllers\Auth']);
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

        switch ($config['driver']) {
            case SessionAdapter::class:
                return $this->createSessionDriver($name, $config);
            case TokenAdapter::class:
                return $this->createTokenDriver($config);
            default:
                throw new InvalidArgumentException(
                    "Auth driver [{$config['driver']}] for guard [{$name}] is not defined."
                );
        }
    }

    /**
     * Create a session based authentication guard.
     *
     * @return AuthenticationInterface
     */
    protected function createSessionDriver(string $name, array $config)
    {
        return new SessionAdapter(
            $name,
            $this->createUserProvider($config['provider']),
            Services::request(),
            Services::response(),
            Services::session()
        );
    }

    /**
     * Create a token based authentication guard.
     *
     * @return AuthenticationInterface
     */
    protected function createTokenDriver(array $config)
    {
        return new TokenAdapter($this->createUserProvider($config['provider']), Services::request());
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
        return $this->customCreators[$config['driver']]($this, $name, $config);
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
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $arguments
     * @return AuthenticationInterface
     */
    public function __call($method, $arguments)
    {
        return $this->guard()->{$method}(...$arguments);
    }
}
