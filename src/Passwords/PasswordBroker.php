<?php

namespace Fluent\Auth\Passwords;

use Closure;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Contracts\PasswordResetRepositoryInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Helpers\Arr;
use UnexpectedValueException;

use function is_null;

class PasswordBroker implements PasswordBrokerInterface
{
    /** @var PasswordResetRepositoryInterface */
    protected $tokens;

    /** @var UserProviderInterface */
    protected $users;

    public function __construct(PasswordResetRepositoryInterface $tokens, UserProviderInterface $users)
    {
        $this->tokens = $tokens;
        $this->users  = $users;
    }

    /**
     * {@inheritdoc}
     */
    public function sendResetLink(array $credentials, ?Closure $callback = null)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if ($this->tokens->recentlyCreatedToken($user)) {
            return static::RESET_THROTTLED;
        }

        $token = $this->tokens->create($user);

        if ($callback) {
            $callback($user, $token);
        } else {
            // Once we have the reset token, we are ready to send the message out to this
            // user with a link to reset their password. We will then redirect back to
            // the current URI having nothing set in the session to indicate errors.
            $user->sendPasswordResetNotification($token);
        }

        return static::RESET_LINK_SENT;
    }

    /**
     * {@inheritdoc}
     */
    public function sendVerifyLink(array $credentials, ?Closure $callback = null)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if (! $user instanceof VerifyEmailInterface) {
            return static::RESET_THROTTLED;
        }

        if ($callback) {
            $callback($user);
        } else {
            // We are ready to send verify the message out to this user with a link
            // to their email. We will then redirect back to the current URI
            // having nothing set in the session to indicate errors.
            $user->sendEmailVerificationNotification();
        }

        return static::VERIFY_LINK_SENT;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(array $credentials, Closure $callback)
    {
        $user = $this->validateReset($credentials);

        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        if (! $user instanceof ResetPasswordInterface) {
            return $user;
        }

        $password = $credentials['password'];

        // Once the reset has been validated, we'll call the given callback with the
        // new password. This gives the user an opportunity to store the password
        // in their persistent storage. Then we'll delete the token and return.
        $callback($user, $password);

        $this->tokens->destroy($user);

        return static::PASSWORD_RESET;
    }

    /**
     * Validate a password reset for the given credentials.
     *
     * @param  array  $credentials
     * @return ResetPasswordInterface|string
     */
    protected function validateReset(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (! $this->tokens->exists($user, $credentials['token'])) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token']);

        $user = $this->users->findByCredentials($credentials);

        if ($user && ! $user instanceof ResetPasswordInterface) {
            throw new UnexpectedValueException('User must implement ResetPasswordInterface.');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(ResetPasswordInterface $user)
    {
        return $this->tokens->create($user);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteToken(ResetPasswordInterface $user)
    {
        $this->tokens->destroy($user);
    }

    /**
     * {@inheritdoc}
     */
    public function tokenExists(ResetPasswordInterface $user, $token)
    {
        return $this->tokens->exists($user, $token);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->tokens;
    }
}
