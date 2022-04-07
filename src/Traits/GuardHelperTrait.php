<?php

namespace Fluent\Auth\Traits;

use CodeIgniter\Events\Events;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\AuthorizableInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

use function is_null;

trait GuardHelperTrait
{
    /** @var UserProviderInterface */
    protected $provider;

    /** @var AuthenticatorInterface|AuthorizableInterface|ResetPasswordInterface|VerifyEmailInterface|HasAccessTokensInterface */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function authenticate()
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException();
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
}
