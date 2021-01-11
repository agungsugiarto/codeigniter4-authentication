<?php

namespace Fluent\Auth\Contracts;

use Fluent\Auth\Entities\User;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Result;

interface AuthenticationInterface
{
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @param array $credentials
     * @throws AuthenticationException
     */
    public function attempt(array $credentials, bool $remember = false): Result;

    /**
     * Checks a user's $credentials to see if they match an existing user.
     *
     * @param array $credentials
     */
    public function check(array $credentials): Result;

    /**
     * Checks if the user is currently logged in.
     */
    public function loggedIn(): bool;

    /**
     * Logs the given user in.
     */
    public function login(AuthenticatorInterface $user, bool $remember = false): bool;

    /**
     * Logs a user in based on their ID.
     *
     * @return mixed
     * @throws AuthenticationException
     */
    public function loginById(int $userId, bool $remember = false);

    /**
     * Logs the current user out.
     *
     * @return mixed
     */
    public function logout();

    /**
     * Removes any remember-me tokens, if applicable.
     *
     * @return mixed
     */
    public function forget(?int $id);

    /**
     * Returns the currently logged in user.
     *
     * @return AuthenticatorInterface|User|null
     */
    public function getUser();
}
