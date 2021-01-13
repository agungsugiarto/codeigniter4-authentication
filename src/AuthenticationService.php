<?php

namespace Fluent\Auth;

use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Exceptions\AuthenticationException;

use function property_exists;

class AuthenticationService
{
    /** @var AuthenticationFactory|AuthenticationInterface */
    protected $authenticate;

    /**
     * The adapter to use for this request.
     *
     * @var string
     */
    protected $adapter = 'default';

    /** @var User */
    protected $user;

    /** @var UserProviderInterface */
    protected $userProvider;

    /**
     * Authentication service constructor.
     *
     * @return void
     */
    public function __construct(AuthenticationFactory $authenticate)
    {
        $this->authenticate = $authenticate->setProvider($this->getProvider());
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
     * Returns the currently logged in user.
     *
     * @return AuthenticatorInterface|User|null
     */
    public function user()
    {
        return $this->getUser();
    }

    /**
     * Returns the currently logged in user id.
     *
     * @return int|null
     */
    public function id()
    {
        return $this->getUser()->id ?? null;
    }

    /**
     * Get the name of the class that handles user persistence.
     *
     * @return UserProviderInterface
     */
    public function getProvider()
    {
        if ($this->userProvider !== null) {
            return $this->userProvider;
        }

        $config = config('Auth');

        if (! property_exists($config, 'userProvider')) {
            throw AuthenticationException::forUnknownUserProvider();
        }

        $className          = $config->userProvider;
        $this->userProvider = new $className();

        return $this->userProvider;
    }

    /**
     * Dynamically call the default adapter instance.
     *
     * @param string $method
     * @param array $arguments
     */
    public function __call($method, $arguments)
    {
        return $this->authenticate->factory($this->adapter)->{$method}(...$arguments);
    }
}
