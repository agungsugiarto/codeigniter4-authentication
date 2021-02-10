<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Session\SessionInterface;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

use function is_null;
use function sha1;

abstract class AbstractAdapter implements AuthenticationInterface
{
    /** @var Auth */
    protected $config;

    /** @var UserProviderInterface */
    protected $provider;

    /** @var AuthenticatorInterface|ResetPasswordInterface|VerifyEmailInterface|HasAccessTokensInterface */
    protected $user;

    /** @var AuthenticatorInterface */
    protected $lastAttempted;

    /** @var boolean */
    protected $loggedOut = false;

    /** @var boolean */
    protected $viaRember = false;

    /** @var boolean */
    protected $recallAttempted = false;

    /** @var string */
    protected $name;

    /** @var IncomingRequest */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /**
     * Abstract adapter class.
     *
     * @param Auth $config
     */
    public function __construct($config, string $name, UserProviderInterface $provider)
    {
        $this->config   = $config;
        $this->provider = $provider;
        $this->name     = $name;
        $this->request  = Services::request();
        $this->response = Services::response();
        $this->session  = Services::session();
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw AuthenticationException::forInvalidUser();
    }

    /**
     * {@inheritdoc}
     */
    public function viaRemember()
    {
        return $this->viaRember;
    }

    /**
     * {@inheritdoc}
     */
    public function hasUser(): bool
    {
        return ! is_null($this->user);
    }

    /**
     * {@inheritdoc}
     */
    public function check(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * {@inheritdoc}
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthId();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(AuthenticatorInterface $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        Events::trigger('fireAuthenticatedEvent', $user);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionName()
    {
        return 'login_' . "{$this->name}_" . sha1(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieName()
    {
        return 'remember_' . "{$this->name}_" . sha1(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function setProvider(UserProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get the request for authentication.
     *
     * @return IncomingRequest
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get the response from authentication.
     *
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }
}
