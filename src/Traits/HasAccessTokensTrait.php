<?php

namespace Fluent\Auth\Traits;

use Fluent\Auth\Entities\AccessToken;
use Fluent\Auth\Models\AccessTokenModel;

use function bin2hex;
use function hash;
use function random_bytes;

/**
 * Trait HasAccessTokensTrait
 *
 * Provides functionality needed to generate, revoke,
 * and retrieve Personal Access Tokens.
 *
 * Intended to be used with User entities.
 */
trait HasAccessTokensTrait
{
    /**
     * Generates a new personal access token for this user.
     *
     * @param array $scopes
     * @return object
     */
    public function generateAccessToken(string $name, array $scopes = ['*'])
    {
        $tokens = new AccessTokenModel();

        $tokens->insert(new AccessToken([
            'user_id' => $this->id,
            'name'    => $name,
            'token'   => hash('sha256', $rawToken = bin2hex(random_bytes(64))),
            'scopes'  => $scopes,
        ]));

        $token = $tokens->find($tokens->insertId());

        $token->raw_token = $rawToken;

        return $token;
    }

    /**
     * Given the token, will retrieve the token to
     * verify it exists, then delete it.
     *
     * @return mixed
     */
    public function revokeAccessToken(string $token)
    {
        $tokens = new AccessTokenModel();

        return $tokens->where('user_id', $this->id)
            ->where('token', hash('sha256', $token))
            ->delete();
    }

    /**
     * Revokes all access tokens for this user.
     *
     * @return mixed
     */
    public function revokeAllAccessTokens()
    {
        $tokens = new AccessTokenModel();

        return $tokens->where('user_id', $this->id)
            ->delete();
    }

    /**
     * Retrieves all personal access tokens for this user.
     *
     * @return array
     */
    public function accessTokens(): array
    {
        $tokens = new AccessTokenModel();

        return $tokens->where('user_id', $this->id)
            ->find();
    }

    /**
     * Given a raw token, will hash it and attemp to
     * locate it within the system.
     *
     * @return AccessToken|null
     */
    public function getAccessToken(?string $token)
    {
        if (empty($token)) {
            return null;
        }

        $tokens = new AccessTokenModel();

        return $tokens->where('user_id', $this->id)
            ->where('token', hash('sha256', $token))
            ->first();
    }

    /**
     * Given the ID, returns the given access token.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenById(int $id)
    {
        $tokens = new AccessTokenModel();

        return $tokens->where('user_id', $this->id)
            ->where('id', $id)
            ->first();
    }

    /**
     * Determines whether the user's token grants permissions to $scope.
     * First checks against $this->activeToken, which is set during
     * authentication. If it hasn't been set, returns false.
     */
    public function tokenCan(string $scope): bool
    {
        if (! $this->currentAccessToken() instanceof AccessToken) {
            return false;
        }

        return $this->currentAccessToken()->can($scope);
    }

    /**
     * Determines whether the user's token does NOT grant permissions to $scope.
     * First checks against $this->activeToken, which is set during
     * authentication. If it hasn't been set, returns true.
     */
    public function tokenCant(string $scope): bool
    {
        if (! $this->currentAccessToken() instanceof AccessToken) {
            return true;
        }

        return $this->currentAccessToken()->cant($scope);
    }

    /**
     * Returns the current access token for the user.
     *
     * @return AccessToken
     */
    public function currentAccessToken()
    {
        return $this->attributes['activeAccessToken'] ?? null;
    }

    /**
     * Sets the current active token for this user.
     *
     * @return $this
     */
    public function withAccessToken(?AccessToken $accessToken)
    {
        $this->attributes['activeAccessToken'] = $accessToken;

        return $this;
    }
}
