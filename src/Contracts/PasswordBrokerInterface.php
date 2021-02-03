<?php

namespace Fluent\Auth\Contracts;

use Closure;
use Fluent\Auth\Contracts\PasswordResetRepositoryInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;

interface PasswordBrokerInterface
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const RESET_LINK_SENT = 'passwords.sent';

    /**
     * Constant representing a successfully reset password.
     *
     * @var string
     */
    const PASSWORD_RESET = 'passwords.reset';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'passwords.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'passwords.token';

    /**
     * Constant representing a throttled reset attempt.
     *
     * @var string
     */
    const RESET_THROTTLED = 'passwords.throttled';

    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @return string
     */
    public function sendResetLink(array $credentials, ?Closure $callback = null);

    /**
     * Reset the password for the given token.
     *
     * @param  array  $credentials
     * @return mixed
     */
    public function reset(array $credentials, Closure $callback);

    /**
     * Get the user for the given credentials.
     *
     * @param  array  $credentials
     * @return ResetPasswordInterface|null
     * @throws UnexpectedValueException
     */
    public function getUser(array $credentials);

    /**
     * Create a new password reset token for the given user.
     *
     * @return string
     */
    public function createToken(ResetPasswordInterface $user);

    /**
     * Delete password reset tokens of the given user.
     *
     * @return void
     */
    public function deleteToken(ResetPasswordInterface $user);

    /**
     * Validate the given password reset token.
     *
     * @param  string  $token
     * @return bool
     */
    public function tokenExists(ResetPasswordInterface $user, $token);

    /**
     * Get the password reset token repository implementation.
     *
     * @return PasswordResetRepositoryInterface
     */
    public function getRepository();
}
