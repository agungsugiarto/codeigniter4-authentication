<?php

namespace Fluent\Auth\Contracts;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

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
     * @param string $token
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByRememberToken(int $id, $token);

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param string $token
     * @return mixed
     */
    public function updateRememberToken(AuthenticatorInterface $user, $token);

    /**
     * Validate a user against the given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatorInterface $user, array $credentials);

    /**
     * Get instance class user provider.
     *
     * @return BaseBuilder|Model
     */
    public function instance();
}
