<?php

namespace Fluent\Auth\Notifications;

use CodeIgniter\Config\Services;
use CodeIgniter\Email\Email;

class EmailResetPasswordNotification
{
    /** @var string */
    protected $email;

    /** @var Email */
    protected $service;

    /**
     * Instance verification notification.
     */
    public function __construct(string $email)
    {
        $this->email   = $email;
        $this->service = Services::email();
    }

    /**
     * Sending email verification.
     *
     * @return bool
     */
    public function send()
    {
        return $this->service
            ->setTo($this->email)
            ->setSubject('Email reset password verification')
            ->setMessage('Email reset password verification notification')
            ->setMailType('html')
            ->send();
    }
}
