<?php

namespace Fluent\Auth\Entities;

use CodeIgniter\Entity;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Traits\Authenticatable;
use Fluent\Auth\Traits\HasAccessTokens;

class User extends Entity implements AuthenticatorInterface, HasAccessTokensInterface
{
    use Authenticatable;
    use HasAccessTokens;

    protected $casts = [
        'active'           => 'boolean',
        'force_pass_reset' => 'boolean',
    ];

    /**
     * Fill set password hash.
     *
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->attributes['password'] = Services::passwords()->hash($password);

        return $this;
    }
}
