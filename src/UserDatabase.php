<?php

namespace Fluent\Auth;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Illuminate\Hashing\Supports\Hash;

use function array_key_exists;
use function count;
use function hash_equals;
use function is_array;
use function mb_strpos;
use function trim;

class UserDatabase implements UserProviderInterface
{
    /**
     * The active database connection.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * The table containing the users.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Create a new database user provider.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = Database::connect();
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id)
    {
        return $this->connection->table($this->table)->where('id', $id)->get()->getFirstRow(User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findByRememberToken(int $id, $token)
    {
        $user = $this->connection->table($this->table)->where(['id' => $id, 'token' => trim($token)])->get()->getFirstRow(User::class);

        return $user && $user->getRememberToken() && hash_equals($user->getRememberToken(), $token)
            ? $user
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRememberToken(AuthenticatorInterface $user, $token)
    {
        return $this->connection->table($this->table)
            ->where($user->getAuthIdColumn(), $user->getAuthId())
            ->set($user->getRememberColumn(), $token)
            ->update();
    }

    /**
     * {@inheritdoc}
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

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // generic "user" object that will be utilized by the Guard instances.
        $query = $this->connection->table($this->table);

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

        // Now we are ready to execute the query to see if we have an user matching
        // the given credentials. If not, we will just return nulls and indicate
        // that there are no matching users for these given credential arrays.
        return $query->get()->getFirstRow(User::class);
    }

    /**
     * {@inheritdoc}
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
