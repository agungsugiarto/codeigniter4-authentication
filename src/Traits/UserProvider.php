<?php

namespace Fluent\Auth\Traits;

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticatorInterface;

use function trim;

trait UserProvider
{
    /**
     * Locates an identity object by ID.
     *
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Locate a user by the given credentials.
     *
     * @param array $credentials
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByCredentials(array $credentials)
    {
        return $this->where('email', $credentials['email'])->orWhere($credentials)->first();
    }

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByRememberToken(int $id, $token)
    {
        return $this->where(['id' => $id, 'remember_token' => trim($token)])->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param string $token
     * @return void
     */
    public function updateRememberToken(AuthenticatorInterface $user, $token = null)
    {
        return $this->where('id', $user->getAuthId())->set('remember_token', $token)->update();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatorInterface $user, array $credentials)
    {
        $plain = $credentials['password'];

        return Services::passwords()->verify($plain, $user->getAuthPassword());
    }
}
