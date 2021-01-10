<?php

namespace Fluent\Auth;

use Fluent\Auth\Config\Auth;

use function base64_encode;
use function defined;
use function hash;
use function password_hash;
use function password_needs_rehash;
use function password_verify;

use const PASSWORD_ARGON2I;
use const PASSWORD_ARGON2ID;

class Passwords
{
    /** @var Auth */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Hash a password.
     *
     * @return false|string|null
     */
    public function hash(string $password)
    {
        if (
            (defined('PASSWORD_ARGON2I') && $this->config->hashAlgorithm === PASSWORD_ARGON2I)
            ||
            (defined('PASSWORD_ARGON2ID') && $this->config->hashAlgorithm === PASSWORD_ARGON2ID)
        ) {
            $hashOptions = [
                'memory_cost' => $this->config->hashMemoryCost,
                'time_cost'   => $this->config->hashTimeCost,
                'threads'     => $this->config->hashThreads,
            ];
        } else {
            $hashOptions = [
                'cost' => $this->config->hashCost,
            ];
        }

        return password_hash(
            base64_encode(
                hash('sha384', $password, true)
            ),
            $this->config->hashAlgorithm,
            $hashOptions
        );
    }

    /**
     * Verifies a password against a previously hashed password.
     *
     * @param string $password The password we're checking
     * @param string $hash     The previously hashed password
     */
    public function verify(string $password, string $hash)
    {
        return password_verify(base64_encode(
            hash('sha384', $password, true)
        ), $hash);
    }

    /**
     * Checks to see if a password should be rehashed.
     *
     * @return boolean
     */
    public function needsRehash(string $hashedPassword)
    {
        return password_needs_rehash($hashedPassword, $this->config->hashAlgorithm);
    }
}
