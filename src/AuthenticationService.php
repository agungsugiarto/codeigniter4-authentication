<?php

namespace Fluent\Auth;

use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Exceptions\AuthenticationException;

use function property_exists;

class AuthenticationService
{
    /** @var AuthenticationFactory|AuthenticationInterface */
    protected $authenticate;

    protected $authorize;

    /**
     * The handler to use for this request.
     *
     * @var string
     */
    protected $handler = 'default';

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
     * Sets the handler that should be used for this request.
     *
     * @return $this
     */
    public function withHandler(?string $handler = null)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Difine routes method.
     */
    public function routes(?array $config = null)
    {
    }

    public function authorize($entity, string $permission)
    {
    }

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
        return $this->authenticate->factory($this->handler)->{$method}(...$arguments);
    }
}
