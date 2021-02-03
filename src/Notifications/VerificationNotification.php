<?php

namespace Fluent\Auth\Notifications;

use CodeIgniter\Config\Services;
use CodeIgniter\Email\Email;
use CodeIgniter\I18n\Time;

class VerificationNotification
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
            ->setSubject('Verify Email Address')
            ->setMessage(view('Fluent\Auth\Views\Email\verify_email', [
                'hash'   => sha1($this->email),
                'expire' => Time::now()->addMinutes(config('Auth')->passwords['users']['expire'])->getTimestamp(),
            ]))
            ->setMailType('html')
            ->send();
    }
}
