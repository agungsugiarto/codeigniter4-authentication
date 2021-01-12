<?php

namespace Fluent\Auth\Models;

use CodeIgniter\Model;
use Faker\Generator;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Traits\UserProvider as UserProviderTrait;

use function password_hash;

use const PASSWORD_DEFAULT;

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
        'reset_hash',
        'reset_at',
        'reset_expires',
        'activate_hash',
        'status',
        'status_message',
        'active',
        'force_pass_reset',
        'deleted_at',
    ];

    protected $useTimestamps = true;

    public function fake(Generator &$faker)
    {
        return [
            'email'                => $faker->email,
            'username'             => $faker->userName,
            'password'             => password_hash('secret', PASSWORD_DEFAULT),
            'active'               => true,
            'force_password_reset' => false,
        ];
    }
}
