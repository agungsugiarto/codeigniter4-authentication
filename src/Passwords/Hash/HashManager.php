<?php

namespace Fluent\Auth\Passwords\Hash;

use Fluent\Auth\Contracts\HasherInterface;
use Fluent\Auth\Passwords\Hash\Argon2IdHasher;
use Fluent\Auth\Passwords\Hash\ArgonHasher;
use Fluent\Auth\Passwords\Hash\BcryptHasher;

class HashManager extends AbstractManager implements HasherInterface
{
    /**
     * Create an instance of the Bcrypt hash Driver.
     *
     * @return BcryptHasher
     */
    public function createBcryptDriver()
    {
        return new BcryptHasher($this->config->bcrypt ?? []);
    }

    /**
     * Create an instance of the Argon2i hash Driver.
     *
     * @return ArgonHasher
     */
    public function createArgonDriver()
    {
        return new ArgonHasher($this->config->argon ?? []);
    }

    /**
     * Create an instance of the Argon2id hash Driver.
     *
     * @return Argon2IdHasher
     */
    public function createArgon2idDriver()
    {
        return new Argon2IdHasher($this->config->argon ?? []);
    }

    /**
     * Get information about the given hashed value.
     *
     * @param  string  $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return $this->driver()->info($hashedValue);
    }

    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array  $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        return $this->driver()->make($value, $options);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        return $this->driver()->check($value, $hashedValue, $options);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string  $hashedValue
     * @param  array  $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return $this->driver()->needsRehash($hashedValue, $options);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->driver;
    }
}
