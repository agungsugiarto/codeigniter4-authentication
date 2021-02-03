<?php

use CodeIgniter\Events\Events;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Notifications\ResetPasswordNotification;
use Fluent\Auth\Notifications\VerificationNotification;

Events::on(VerifyEmailInterface::class, function ($email) {
    (new VerificationNotification($email))->send();
});

Events::on(ResetPasswordInterface::class, function ($email, $token) {
    (new ResetPasswordNotification($email, $token))->send();
});
