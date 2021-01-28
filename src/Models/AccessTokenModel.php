<?php

namespace Fluent\Auth\Models;

use CodeIgniter\Model;
use Faker\Generator;
use Fluent\Auth\Entities\AccessToken;

use function hash;

class AccessTokenModel extends Model
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $table = 'auth_access_tokens';

    /**
     * The format that the results should be returned as.
     * Will be overridden if the as* methods are used.
     *
     * @var AccessToken
     */
    protected $returnType = AccessToken::class;

    /**
     * An array of field names that are allowed
     * to be set by the user in inserts/updates.
     *
     * @var array
     */
    protected $allowedFields = [
        'user_id',
        'name',
        'token',
        'last_used_at',
        'scopes',
    ];

    /**
     * If true, will set created_at, and updated_at
     * values during insert and update routines.
     *
     * @var boolean
     */
    protected $useTimestamps = true;

    /**
     * Generate fake data.
     *
     * @return array
     */
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
