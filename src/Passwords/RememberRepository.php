<?php

namespace Fluent\Auth\Passwords;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\CanResetPasswordInterface;
use Fluent\Auth\Contracts\RememberRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;

use function bin2hex;
use function hash_hmac;

class RememberRepository extends Model implements RememberRepositoryInterface
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $table = 'auth_password_resets';

    /**
     * The format that the results should be returned as.
     * Will be overridden if the as* methods are used.
     *
     * @var string
     */
    protected $returnType = 'object';

    /**
     * If true, will set created_at, and updated_at
     * values during insert and update routines.
     *
     * @var boolean
     */
    protected $useTimestamps = true;

    /**
     * An array of field names that are allowed
     * to be set by the user in inserts/updates.
     *
     * @var array
     */
    protected $allowedFields = ['email', 'token'];

    /**
     * {@inheritdoc}
     */
    public function create(CanResetPasswordInterface $user)
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken();

        $this->insert($this->getPayload($email, $token));

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', bin2hex(random_string(20)), config('Encryption')->key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(CanResetPasswordInterface $user, $token)
    {
        $record = $this->where('email', $user->getEmailForPasswordReset())->first();

        return $record && ! $this->tokenExpired($record->created_at) && Services::passwords()->verify($token, $record->token);
    }

    /**
     * {@inheritdoc}
     */
    public function recentlyCreatedToken(CanResetPasswordInterface $user)
    {
        $record = $this->where('email', $user->getEmailForPasswordReset())->first();

        return $record && $this->tokenRecentlyCreated($record->created_at);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(CanResetPasswordInterface $user)
    {
        return $this->deleteExisting($user);
    }

    /**
     * {@inheritdoc}
     */
    public function destoryExpired()
    {
        $expiredAt = Time::now()->subSeconds(config('Auth')->password['expire']);

        $this->where('created_at <', $expiredAt)->delete();
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param CanResetPassword $user
     * @return int
     */
    protected function deleteExisting(CanResetPasswordInterface $user)
    {
        return $this->where('email', $user->getEmailForPasswordReset())->delete();
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
        return ['email' => $email, 'token' => Services::passwords()->hash($token), 'created_at' => Time::now()];
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        $past = Time::parse($createdAt)->addSeconds(config('Auth')->password['expire']);

        return $past->isBefore($past);
    }

    /**
     * Determine if the token was recently created.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenRecentlyCreated($createdAt)
    {
        if (config('Auth')->passwords['throttle'] <= 0) {
            return false;
        }

        $after = Time::parse($createdAt)->addSeconds(config('Auth')->passwords['throttle']);

        return $after->isAfter($after);
    }
}
