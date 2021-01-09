<?php

namespace Fluent\Auth\Models;

use CodeIgniter\Model;
use Faker\Generator;
use Fluent\Auth\Entities\AccessToken;

use function hash;

class AccessTokenModel extends Model
{
    protected $table      = 'auth_access_tokens';
    protected $primaryKey = 'id';

    protected $returnType     = AccessToken::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'name',
        'token',
        'last_used_at',
        'scopes',
    ];

    protected $useTimestamps = true;

    public function fake(Generator &$faker)
    {
        helper('text');
        $rawToken = random_string('crypt', 64);

        return [
            'user_id'   => fake(UserModel::class)->id,
            'name'      => $faker->streetName,
            'token'     => hash('sha256', $rawToken),
            'raw_token' => $rawToken, // Only normally available on newly created record.
        ];
    }
}
