<?php

namespace Fluent\Authentication\Contracts;

use Fluent\Authentication\Result;

interface AuthenticationInterface
{
    /**
     * Authentication and provides an authentication result.
     *
     * @return Result
     */
    public function authenticate();

    /**
     * Return true if and only if an identity is avaliable.
     *
     * @return bool
     */
    public function hasIdentity();

    /**
     * Return the authenticated identity or null if no identity i avaliable.
     *
     * @return mixed|null
     */
    public function getIdentity();

    /**
     * Clear the identity.
     */
    public function cleanIdentity();
}
