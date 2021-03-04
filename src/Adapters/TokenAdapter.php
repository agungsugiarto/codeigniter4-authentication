<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RequestInterface;
use Exception;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Models\AccessTokenModel;
use Fluent\Auth\Traits\GuardHelperTrait;

use function hash;
use function is_null;
use function preg_replace;
use function trim;

class TokenAdapter implements AuthenticationInterface
{
    use GuardHelperTrait;

    /** @var boolean */
    protected $loggedOut = false;

    /** @var RequestInterface */
    protected $request;

    public function __construct(UserProviderInterface $provider, RequestInterface $request)
    {
        $this->provider = $provider;
        $this->request  = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionName()
    {
        throw new Exception('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieName()
    {
        throw new Exception('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function viaRemember()
    {
        throw new Exception('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function attempt(array $credentials, bool $remember = false)
    {
        Events::trigger('fireAttemptEvent', $credentials, $remember);

        if ($user = $this->hasValidCredentials($credentials)) {
            $this->login($user, false);

            return true;
        }

        Events::trigger('fireFailedEvent', $user, $credentials);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $credentials): bool
    {
        if (empty($credentials['token'])) {
            return false;
        }

        $credentials = ['token' => hash('sha256', $credentials['token'])];

        if ($this->accessToken()->where($credentials)->first()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function login(AuthenticatorInterface $user, bool $remember = false)
    {
        $this->setUser($user);

        Events::trigger('fireLoginEvent', $user, false);

        // Provide codeigniter4/authentitication-implementation
        Events::trigger('login', $user, false);

        /** @var HasAccessTokensInterface $user */
        $user->withAccessToken(
            $user->getAccessToken($this->getTokenForRequest())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loginById($userId, bool $remember = false)
    {
        if (! is_null($user = $this->provider->findById($userId))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        $user = $this->user();

        Events::trigger('fireLogoutEvent', $user);

        // Provide codeigniter4/authentitication-implementation
        Events::trigger('logout', $user);

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();

        if (! is_null($token)) {
            $credentials = ['token' => $token];
            Events::trigger('fireAuthenticatedEvent', $this->user);

            if ($user = $this->hasValidCredentials($credentials)) {
                $this->login($user);
            }
        }

        return $this->user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    protected function getTokenForRequest()
    {
        $token = $this->request->getGet('token');

        if (empty($token)) {
            $token = $this->bearerToken();
        }

        return $token;
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    protected function bearerToken()
    {
        if (empty($header = $this->request->getHeaderLine('Authorization'))) {
            return null;
        }

        return trim((string) preg_replace('/^(?:\s+)?Token\s/', '', $header)) ?? null;
    }

    /**
     * Intance access token model.
     *
     * @return AccessTokenModel
     */
    protected function accessToken()
    {
        return new AccessTokenModel();
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  array  $credentials
     * @return AuthenticatorInterface|HasAccessTokensInterface|null
     */
    protected function hasValidCredentials(array $credentials)
    {
        if (empty($credentials['token'])) {
            return false;
        }

        $credentials = ['token' => hash('sha256', $credentials['token'])];

        if ($token = $this->accessToken()->where($credentials)->first()) {
            Events::trigger('fireValidatedEvent', $token->user());
            return $token->user();
        }

        return false;
    }
}
