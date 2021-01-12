<?php

namespace Fluent\Auth\Config;

use CodeIgniter\Config\BaseConfig;
use Fluent\Auth\Adapters\SessionAdapter;
use Fluent\Auth\Adapters\TokenAdapter;
use Fluent\Auth\Models\UserModel;

use const PASSWORD_DEFAULT;

class Auth extends BaseConfig
{
    /**
     * The available authentication systems, list with alias and class name.
     * Default adapter is using first key array of authenticators.
     * These can be referenced by alias in the auth helper:
     *      auth('token')->attempt($credentials);
     */
    public $authenticators = [
        'session' => SessionAdapter::class,
        'token'   => TokenAdapter::class,
    ];

    /**
     * The name of the class that handles user persistence.
     * By default, this is the included UserModel, which
     * works with any of the database engines supported by CodeIgniter.
     */
    public $userProvider = UserModel::class;

    /**
     * Session config.
     */
    public $sessionConfig = [
        'field'              => 'logged_in',
        'allowRemembering'   => true,
        'rememberCookieName' => 'remember',
        'rememberLength'     => 30 * DAY,
    ];

    /**
     * --------------------------------------------------------------------
     * Encryption Algorithm to use
     * --------------------------------------------------------------------
     * Valid values are
     * - PASSWORD_DEFAULT
     * - PASSWORD_BCRYPT (deafault)
     * - PASSWORD_ARGON2I  - As of PHP 7.2 only if compiled with support for it
     * - PASSWORD_ARGON2ID - As of PHP 7.3 only if compiled with support for it
     *
     * If you choose to use any ARGON algorithm, then you might want to
     * uncomment the "ARGON2i/D Algorithm" options to suit your needs
     */
    public $hashAlgorithm = PASSWORD_DEFAULT;

    /**
     * --------------------------------------------------------------------
     * ARGON2i/D Algorithm options
     * --------------------------------------------------------------------
     * The ARGON2I method of encryption allows you to define the "memory_cost",
     * the "time_cost" and the number of "threads", whenever a password hash is
     * created.
     * This defaults to a value of 10 which is an acceptable number.
     * However, depending on the security needs of your application
     * and the power of your hardware, you might want to increase the
     * cost. This makes the hashing process takes longer.
     */
    public $hashMemoryCost = 2048; // PASSWORD_ARGON2_DEFAULT_MEMORY_COST;
    public $hashTimeCost   = 4;    // PASSWORD_ARGON2_DEFAULT_TIME_COST;
    public $hashThreads    = 4;    // PASSWORD_ARGON2_DEFAULT_THREADS;

    /**
     * --------------------------------------------------------------------
     * Password Hashing Cost
     * --------------------------------------------------------------------
     * The BCRYPT method of encryption allows you to define the "cost"
     * or number of iterations made, whenever a password hash is created.
     * This defaults to a value of 10 which is an acceptable number.
     * However, depending on the security needs of your application
     * and the power of your hardware, you might want to increase the
     * cost. This makes the hashing process takes longer.
     *
     * Valid range is between 4 - 31.
     */
    public $hashCost = 10;

    /**
     * Throttler login max request attempt within a minute.
     */
    public $throttler = 60;
}
