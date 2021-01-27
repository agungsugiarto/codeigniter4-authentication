<?php

namespace Fluent\Auth\Adapters;

use Fluent\Auth\Contracts\AuthenticatorInterface;

class TokenAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function attempt(array $credentials, bool $remember = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $credentials): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function loggedIn(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function login(AuthenticatorInterface $user, bool $remember = false): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function loginById(int $userId, bool $remember = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
    }
}
