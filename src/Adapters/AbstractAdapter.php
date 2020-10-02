<?php

namespace Fluent\Authentication\Adapters;

use Fluent\Authentication\Contracts\ValidatableAdapterInterface;

abstract class AbstractAdapter implements ValidatableAdapterInterface
{
    /** @var mixed */
    protected $credential;

    /** @var mixed */
    protected $identity;

    /**
     * Returns the credential of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Sets the credential for binding
     *
     * @param  mixed $credential
     * @return $this Provides a fluent interface
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Returns the identity of the account being authenticated, or
     * NULL if none is set.
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Sets the identity for binding
     *
     * @param  mixed $identity
     * @return $this Provides a fluent interface
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }
}
