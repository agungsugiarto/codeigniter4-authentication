<?php

namespace Fluent\Auth\Traits;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Facades\Hash;
use Fluent\Auth\Helpers\Str;

use function array_key_exists;
use function count;
use function hash_equals;
use function is_array;

trait UserProviderTrait
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
        if (
            empty($credentials) ||
            (count($credentials) === 1 &&
            array_key_exists('password', $credentials))
        ) {
            return;
        }

        /** @var Model $query */
        $query = clone $this;

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Find a user by their ID and "remember-me" token.
     *
     * @param string $token
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByRememberToken(int $id, $token)
    {
        $retrievedModel = $this->where('id', $id)->first();

        if (! $retrievedModel) {
            return;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel
            : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param string $token
     * @return mixed
     */
    public function updateRememberToken(AuthenticatorInterface $user, $token = null)
    {
        return $this->where($user->getAuthIdColumn(), $user->getAuthId())
            ->set($user->getRememberColumn(), $token)->update();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(AuthenticatorInterface $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Get instance class user provider.
     *
     * @return BaseBuilder|Model
     */
    public function instance()
    {
        return $this;
    }
}
