<?php

namespace Fluent\Auth\Traits;

use CodeIgniter\Model;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Illuminate\Hashing\Supports\Hash;

use function array_key_exists;
use function count;
use function hash_equals;
use function is_array;
use function mb_strpos;
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
            if (static::contains($key, 'password')) {
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
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    public function findByRememberToken(int $id, $token)
    {
        $user = $this->where(['id' => $id, 'remember_token' => trim($token)])->first();

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)
            ? $user
            : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param string $token
     * @return void
     */
    public function updateRememberToken(AuthenticatorInterface $user, $token = null)
    {
        return $this->where($user->getAuthIdColumn(), $user->getAuthId())->set($user->getRememberColumn(), $token)->update();
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
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|string[]  $needles
     * @return bool
     */
    protected static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
