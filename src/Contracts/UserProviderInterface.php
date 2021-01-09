<?php

namespace Fluent\Auth\Contracts;

interface UserProviderInterface
{
    /**
     * Locates an identity object by ID.
     *
     * @return AuthenticatorInterface|null
     */
    public function findById(int $id);

    /**
     * Locate a user by the given credentials.
     *
     * @param array $credentials
     * @return AuthenticatorInterface|null
     */
    public function findByCredentials(array $credentials);

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @return AuthenticatorInterface|null
     */
    public function findByRememberToken(int $id, string $token);

    /**
     * Given a user object, will update the record
     * in the database. This is often a User entity
     * that already has the correct values set.
     *
     * @param $data
     */
    public function save($data);
}
