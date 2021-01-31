<?php

namespace Fluent\Auth\Traits;

use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Fluent\Auth\Contracts\MustVerifyEmailInterface;

use function is_null;

trait MustVerifyEmail
{
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        /** @var Model $config */
        $config = config('Auth')->password['provider'];

        return (new $config())->where('email', $this->getEmailForVerification())->set('email_verified_at', Time::now())->update();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        Events::trigger(MustVerifyEmailInterface::class, $this->getEmailForVerification());
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }
}