<?php

namespace Fluent\Auth\Entities;

use CodeIgniter\Entity;

use function in_array;

class AccessToken extends Entity
{
    protected $casts = [
        'last_used_at' => 'datetime',
        'scopes'       => 'array',
        'expires'      => 'datetime',
    ];

    /**
     * Returns the user associated with this token.
     *
     * @return mixed
     */
    public function user()
    {
        if (empty($this->attributes['user'])) {
            helper('auth');
            $users                    = auth()->getProvider();
            $this->attributes['user'] = $users->findById($this->user_id);
        }

        return $this->attributes['user'];
    }

    /**
     * Determines whether this token grants
     * permission to the $scope
     */
    public function can(string $scope): bool
    {
        if (empty($this->scopes)) {
            return false;
        }

        // Wildcard present
        if (in_array('*', $this->scopes)) {
            return true;
        }

        // Check stored scopes
        return in_array($scope, $this->scopes);
    }

    /**
     * Determines whether this token does NOT
     * grant permission to $scope.
     */
    public function cant(string $scope): bool
    {
        if (empty($this->scopes)) {
            return true;
        }

        // Wildcard present
        if (in_array('*', $this->scopes)) {
            return false;
        }

        // Check stored scopes
        return ! in_array($scope, $this->scopes);
    }
}
