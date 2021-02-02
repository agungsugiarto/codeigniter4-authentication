<?php

namespace Fluent\Auth\Passwords;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Fluent\Auth\Contracts\PasswordResetInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Facades\Hash;

use function bin2hex;
use function hash_hmac;
use function random_bytes;

class PasswordResetRepository extends Model implements PasswordResetInterface
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
     */
    public function __construct(int $expires = 60, int $throttle = 60)
    {
        parent::__construct();

        $this->expires  = $expires * 60;
        $this->throttle = $throttle;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ResetPasswordInterface $user)
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
        return hash_hmac('sha256', bin2hex(random_bytes(20)), config('Encryption')->key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(ResetPasswordInterface $user, $token)
    {
        $record = $this->where('email', $user->getEmailForPasswordReset())->first();

        return $record && ! $this->tokenExpired($record->created_at) && Hash::check($token, $record->token);
    }

    /**
     * {@inheritdoc}
     */
    public function recentlyCreatedToken(ResetPasswordInterface $user)
    {
        $record = $this->where('email', $user->getEmailForPasswordReset())->first();

        return $record && $this->tokenRecentlyCreated($record->created_at);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(ResetPasswordInterface $user)
    {
        return $this->deleteExisting($user);
    }

    /**
     * {@inheritdoc}
     */
    public function destroyExpired()
    {
        $expiredAt = Time::now()->subSeconds($this->expires);

        $this->where('created_at <', $expiredAt)->delete();
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @return int
     */
    protected function deleteExisting(ResetPasswordInterface $user)
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
        return ['email' => $email, 'token' => Hash::make($token), 'created_at' => Time::now()];
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        $past = Time::parse($createdAt)->addSeconds($this->expires);

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
        if ($this->throttle <= 0) {
            return false;
        }

        $after = Time::parse($createdAt)->addSeconds($this->throttle);

        return $after->isAfter($after);
    }
}
