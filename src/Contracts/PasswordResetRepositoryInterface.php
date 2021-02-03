<?php

namespace Fluent\Auth\Contracts;

use Fluent\Auth\Contracts\ResetPasswordInterface;

interface PasswordResetRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @return string
     */
    public function create(ResetPasswordInterface $user);

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken();

    /**
     * Determine if a token record exists and is valid.
     *
     * @param string $token
     * @return bool
     */
    public function exists(ResetPasswordInterface $user, $token);

    /**
     * Determine if the given user recently created a password reset token.
     *
     * @return bool
     */
    public function recentlyCreatedToken(ResetPasswordInterface $user);

    /**
     * Destroy a token record.
     *
     * @return void
     */
    public function destroy(ResetPasswordInterface $user);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function destroyExpired();
}
