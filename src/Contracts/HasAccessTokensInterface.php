<?php

namespace Fluent\Auth\Contracts;

use Fluent\Auth\Entities\AccessToken;

interface HasAccessTokensInterface
{
    /**
     * Generates a new personal access token for this user.
     *
     * @param array $scopes
     * @return object
     */
    public function generateAccessToken(string $name, array $scopes = ['*']);

    /**
     * Given the token, will retrieve the token to
     * verify it exists, then delete it.
     *
     * @return mixed
     */
    public function revokeAccessToken(string $token);

    /**
     * Revokes all access tokens for this user.
     *
     * @return mixed
     */
    public function revokeAllAccessTokens();

    /**
     * Retrieves all personal access tokens for this user.
     *
     * @return array
     */
    public function accessTokens(): array;

    /**
     * Given a raw token, will hash it and attemp to
     * locate it within the system.
     *
     * @return AccessToken|null
     */
    public function getAccessToken(?string $token);

    /**
     * Given the ID, returns the given access token.
     *
     * @return AccessToken|null
     */
    public function getAccessTokenById(int $id);

    /**
     * Determines whether the user's token grants permissions to $scope.
     * First checks against $this->activeToken, which is set during
     * authentication. If it hasn't been set, returns false.
     */
    public function tokenCan(string $scope): bool;

    /**
     * Determines whether the user's token does NOT grant permissions to $scope.
     * First checks against $this->activeToken, which is set during
     * authentication. If it hasn't been set, returns true.
     */
    public function tokenCant(string $scope): bool;

    /**
     * Returns the current access token for the user.
     *
     * @return AccessToken
     */
    public function currentAccessToken();

    /**
     * Sets the current active token for this user.
     *
     * @return $this
     */
    public function withAccessToken(?AccessToken $accessToken);
}
