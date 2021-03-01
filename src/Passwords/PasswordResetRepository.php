<?php

namespace Fluent\Auth\Passwords;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Config;
use CodeIgniter\I18n\Time;
use Fluent\Auth\Contracts\PasswordResetRepositoryInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Facades\Hash;

use function bin2hex;
use function hash_hmac;
use function random_bytes;

class PasswordResetRepository implements PasswordResetRepositoryInterface
{
    /**
     * The active database connection.
     *
     * @var BaseBuilder
     */
    protected $connection;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Minimum number of seconds before re-redefining the token.
     *
     * @var int
     */
    protected $throttle;

    /**
     * Create new token repository instance.
     * 
     * @param string|null $connection
     * @return void
     */
    public function __construct(string $table, $connection = null, int $expires = 60, int $throttle = 60)
    {
        $this->connection = Config::connect($connection)->table($table);
        $this->expires    = $expires * 60;
        $this->throttle   = $throttle;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ResetPasswordInterface $user)
    {
        $email = $user->getEmailForPasswordReset();

        $this->destroy($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->connection->insert($this->getPayload($email, $token));

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', bin2hex(random_bytes(20)), config('Encryption')->key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(ResetPasswordInterface $user, $token)
    {
        $record = $this->connection->where('email', $user->getEmailForPasswordReset())->get()->getFirstRow();

        $expiredAt = Time::now()->subSeconds($this->expires);

        return $record && ! $record->created_at < $expiredAt && Hash::check($token, $record->token);
    }

    /**
     * {@inheritdoc}
     */
    public function recentlyCreatedToken(ResetPasswordInterface $user)
    {
        if ($this->throttle <= 0) {
            return false;
        }

        $record = $this->connection->where('email', $user->getEmailForPasswordReset())->get()->getFirstRow();

        $expiredAt = Time::now()->subSeconds($this->throttle);

        return $record && $record->created_at > $expiredAt;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(ResetPasswordInterface $user)
    {
        return $this->connection->where('email', $user->getEmailForPasswordReset())->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function destroyExpired()
    {
        $expiredAt = Time::now()->subSeconds($this->expires);

        return $this->connection->where('created_at <', $expiredAt)->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  string  $email
     * @param  string  $token
     * @return array
     */
    protected function getPayload($email, $token)
    {
        return [
            'email'      => $email,
            'token'      => Hash::make($token),
            'created_at' => Time::now(),
            'updated_at' => Time::now(),
        ];
    }
}
