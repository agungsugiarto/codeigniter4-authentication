<?php

namespace Fluent\Auth\Contracts;

use ReflectionException;

interface UserProviderInterface
{
    /**
     * Locates an identity object by ID.
     *
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findById(int $id);

    /**
     * Locate a user by the given credentials.
     *
     * @param array $credentials
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByCredentials(array $credentials);

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByRememberToken(int $id, string $token);

    /**
     * A convenience method that will attempt to determine whether the
     * data should be inserted or updated. Will work with either
     * an array or object. When using with custom class objects,
     * you must ensure that the class will provide access to the class
     * variables, even if through a magic method.
     *
     * @param array|object $data
     * @return boolean
     * @throws ReflectionException
     */
    public function save($data);
}
