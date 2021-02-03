<?php

namespace Fluent\Auth\Traits;

use CodeIgniter\Events\Events;
use Fluent\Auth\Contracts\ResetPasswordInterface;

trait CanResetPasswordTrait
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @return void
     */
    public function sendPasswordResetNotification(string $token)
    {
        Events::trigger(ResetPasswordInterface::class, $this->getEmailForPasswordReset(), $token);
    }
}
