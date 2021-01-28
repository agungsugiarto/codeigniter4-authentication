<?php

namespace Fluent\Auth\Traits;

use CodeIgniter\Model;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticatorInterface;

use function count;
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
            static::contains(static::firstCredentialKey($credentials), 'password'))
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

    /**
     * Get the first key from the credential array.
     *
     * @param  array  $credentials
     * @return string|null
     */
    protected static function firstCredentialKey(array $credentials)
    {
        foreach ($credentials as $key => $value) {
            return $key;
        }
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
