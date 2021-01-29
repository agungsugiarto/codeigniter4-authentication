<?php

namespace Fluent\Auth\Entities;

use CodeIgniter\Entity;
use Fluent\Auth\Contracts\AuthenticatorInterface;
// use Fluent\Auth\Contracts\CanResetPasswordInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
// use Fluent\Auth\Contracts\MustVerifyEmailInterface;
use Fluent\Auth\Traits\Authenticatable;
use Fluent\Auth\Traits\CanResetPassword;
use Fluent\Auth\Traits\HasAccessTokens;
use Fluent\Auth\Traits\MustVerifyEmail;
use Illuminate\Hashing\Supports\Hash;

class User extends Entity implements
    AuthenticatorInterface,
    // CanResetPasswordInterface,
    // MustVerifyEmailInterface,
    HasAccessTokensInterface
{
    use Authenticatable;
    use CanResetPassword;
    use HasAccessTokens;
    use MustVerifyEmail;

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
