<?php

namespace Fluent\Auth\Notifications;

use CodeIgniter\Config\Services;
use CodeIgniter\Email\Email;

class EmailVerificationNotification
{
    /** @var string */
    protected $email;

    /** @var string */
    protected $token;

    /** @var Email */
    protected $service;

    /**
     * Instance verification notification.
     */
    public function __construct(string $email, string $token)
    {
        $this->email   = $email;
        $this->token   = $token;
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
            ->setSubject('Email verification')
            ->setMessage('Email verification notification')
            ->setMailType('html')
            ->send();
    }
}
