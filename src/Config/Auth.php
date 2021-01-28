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
     * --------------------------------------------------------------------------
     * Authentication Defaults
     * --------------------------------------------------------------------------
     * This option controls the default authentication "adapter".
     * You may change these defaults as required,
     * but they're a perfect start for
     * most applications.
     */
    public $defaults = [
        'adapter' => 'session',
    ];

    /**
     * --------------------------------------------------------------------------
     * Authentication Adapters
     * --------------------------------------------------------------------------
     * Next, you may define every authentication adapter for your application.
     * Of course, a great default configuration has been defined for you
     * here which uses session storage and the user provider.
     *
     * All authentication drivers have a user provider. This defines how the
     * users are actually retrieved out of your database or other storage
     * mechanisms used by this application to persist your user's data.
     *
     * Supported: "session", "token"
     */
    public $adapters = [
        'session' => [
            'driver'   => SessionAdapter::class,
            'provider' => UserModel::class,
        ],
        'token'   => [
            'driver'   => TokenAdapter::class,
            'provider' => UserModel::class,
        ],
        // etc your implementation
    ];

    /**
     * --------------------------------------------------------------------------
     * Resetting Passwords
     * --------------------------------------------------------------------------
     *
     * You may specify multiple password reset configurations if you have more
     * than one user table or model in the application and you want to have
     * separate password reset settings based on the specific user types.
     *
     * The expire time is the number of minutes that the reset token should be
     * considered valid. This security feature keeps tokens short-lived so
     * they have less time to be guessed. You may change this as needed.
     */
    public $passwords = [
        'provider' => UserModel::class,
        'table'    => 'password_resets',
        'expire'   => 1 * HOUR,
        'throttle' => 60,
    ];

    /**
     * --------------------------------------------------------------------------
     * Password Confirmation Timeout
     * --------------------------------------------------------------------------
     *
     * Here you may define the amount of seconds before a password confirmation
     * times out and the user is prompted to re-enter their password via the
     * confirmation screen. By default, the timeout lasts for three hours.
     */
    public $password_timeout = 3 * HOUR;

    /**
     * Session config.
     */
    public $sessionConfig = [
        'field'              => 'logged_in',
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
}
