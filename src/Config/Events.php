<?php

use CodeIgniter\Events\Events;
use Fluent\Auth\Contracts\CanResetPasswordInterface;
use Fluent\Auth\Contracts\MustVerifyEmailInterface;
use Fluent\Auth\Notifications\EmailResetPasswordNotification;
use Fluent\Auth\Notifications\EmailVerificationNotification;

Events::on(MustVerifyEmailInterface::class, function ($email, $token) {
    return (new EmailVerificationNotification($email, $token))->send();
});

Events::on(CanResetPasswordInterface::class, function ($email) {
    return (new EmailResetPasswordNotification($email))->send();
});
