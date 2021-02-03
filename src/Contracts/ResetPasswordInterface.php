<?php

namespace Fluent\Auth\Contracts;

interface ResetPasswordInterface
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset();

    /**
     * Send the password reset notification.
     *
     * @return void
     */
    public function sendPasswordResetNotification(string $token);
}
