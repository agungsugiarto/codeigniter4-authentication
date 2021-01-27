<?php

namespace Fluent\Auth;

use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;

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
        $this->authenticate = $authenticate;
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
