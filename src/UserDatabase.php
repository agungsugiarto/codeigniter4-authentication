<?php

namespace Fluent\Auth;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Config;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Facades\Hash;
use Fluent\Auth\Helpers\Str;

use function array_key_exists;
use function count;
use function hash_equals;
use function is_array;

class UserDatabase implements UserProviderInterface
{
    /**
     * The active database connection.
     *
     * @var BaseBuilder
     */
    protected $connection;

    /**
     * Create a new database user provider.
     *
     * @param string|null $connection
     * @return void
     */
    public function __construct(string $table, $connection = null)
    {
        $this->connection = Config::connect($connection)->table($table);
    }

    /**
     * {@inheritdoc}
     */
    public function findById($userId)
    {
        return $this->connection->where('id', $userId)->get()->getFirstRow(User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findByRememberToken($userId, $token)
    {
        $retriveDatabase = $this->connection->where('id', $userId)->get()->getFirstRow(User::class);

        $rememberToken = $retriveDatabase->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retriveDatabase
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRememberToken(AuthenticatorInterface $user, $token)
    {
        return $this->connection
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
        $query = $this->connection;

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
     * {@inheritdoc}
     */
    public function instance()
    {
        return $this->connection;
    }
}
