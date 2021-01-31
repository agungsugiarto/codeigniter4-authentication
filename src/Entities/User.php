<?php

namespace Fluent\Auth\Entities;

use CodeIgniter\Entity;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Facades\Hash;
use Fluent\Auth\Traits\Authenticatable;
use Fluent\Auth\Traits\CanResetPassword;
use Fluent\Auth\Traits\HasAccessTokens;
use Fluent\Auth\Traits\MustVerifyEmail;

class User extends Entity implements
    AuthenticatorInterface,
    HasAccessTokensInterface,
    ResetPasswordInterface,
    VerifyEmailInterface
{
    use Authenticatable;
    use CanResetPassword;
    use HasAccessTokens;
    use MustVerifyEmail;

    /**
     * Array of field names and the type of value to cast them as
     * when they are accessed.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Fill set password hash.
     *
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->attributes['password'] = Hash::make($password);

        return $this;
    }
}
