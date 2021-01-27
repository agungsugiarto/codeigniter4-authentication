<?php

namespace Fluent\Auth\Models;

use CodeIgniter\Model;
use Faker\Generator;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Traits\UserProvider as UserProviderTrait;

class UserModel extends Model implements UserProviderInterface
{
    use UserProviderTrait;

    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $returnType     = User::class;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'email',
        'username',
        'password',
        'email_verified_at',
        'remember_token',
    ];

    protected $useTimestamps = true;

    public function fake(Generator &$faker)
    {
        return [
            'email'    => $faker->email,
            'username' => $faker->userName,
            'password' => 'secret',
        ];
    }
}
