<?php

namespace Fluent\Auth\Entities;

use CodeIgniter\Entity;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Traits\Authenticatable;
use Fluent\Auth\Traits\HasAccessTokens;

class User extends Entity implements AuthenticatorInterface
{
    use Authenticatable;
    use HasAccessTokens;

    protected $casts = [
        'active'           => 'boolean',
        'force_pass_reset' => 'boolean',
    ];
}
