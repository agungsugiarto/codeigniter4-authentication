<?php

namespace Fluent\Auth\Traits;

use Fluent\Auth\Contracts\AuthenticatorInterface;

use function trim;

trait UserProvider
{
    /**
     * Locates an identity object by ID.
     *
     * @return AuthenticatorInterface|null
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Locate a user by the given credentials.
     *
     * @param array $credentials
     * @return AuthenticatorInterface|null
     */
    public function findByCredentials(array $credentials)
    {
        return $this->where($credentials)->first();
    }

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @return AuthenticatorInterface|null
     */
    public function findByRememberToken(int $id, string $token)
    {
        return $this->where([
            'id'          => $id,
            'remember_me' => trim($token),
        ])->first();
    }
}
