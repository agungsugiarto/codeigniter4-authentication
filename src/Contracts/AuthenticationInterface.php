<?php

namespace Fluent\Auth\Contracts;

interface AuthenticationInterface
{
    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @param array   $credentials
     * @return mixed
     * @throws AuthenticationException
     */
    public function attempt(array $credentials, bool $remember = false);

    /**
     * Checks a user's $credentials to see if they match an existing user.
     *
     * @param array $credentials
     * @return mixed
     */
    public function check(array $credentials);

    /**
     * Checks if the user is currently logged in.
     */
    public function loggedIn(): bool;

    /**
     * Logs the given user in.
     *
     * @param Authenticatable $user
     * @return mixed
     */
    public function login(AuthenticatorInterface $user, bool $remember = false);

    /**
     * Logs a user in based on their ID.
     *
     * @return mixed
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
     * @return AuthenticatorInterface|null
     */
    public function getUser();
}
