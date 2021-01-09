<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Events\Events;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Models\AccessTokenModel;
use Fluent\Auth\Models\LoginModel;
use Fluent\Auth\Result;

use function array_key_exists;
use function hash;
use function strpos;
use function substr;
use function trim;

class AccessTokens implements AuthenticationInterface
{
    /** @var array */
    protected $config;

    /**
     * The persistence engine
     */
    protected $provider;

    /** @var AuthenticatorInterface */
    protected $user;

    /** @var LoginModel */
    protected $loginModel;

    public function __construct(Auth $config, $provider)
    {
        $this->config     = $config;
        $this->provider   = $provider;
        $this->loginModel = model(LoginModel::class);
    }

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     *
     * @param array   $credentials
     * @return mixed
     * @throws AuthenticationException
     */
    public function attempt(array $credentials, bool $remember = false)
    {
        $ipAddress = service('request')->getIPAddress();
        $result    = $this->check($credentials);

        if (! $result->isOK()) {
            // Always record a login attempt, whether success or not.
            $this->loginModel->recordLoginAttempt('token: ' . ($credentials['token'] ?? ''), false, $ipAddress, null);

            $this->user = null;

            // Fire an event on failure so devs have the chance to
            // let them know someone attempted to login to their account
            Events::trigger('failedLoginAttempt', $credentials);

            return $result;
        }

        $user = $result->extraInfo();

        $user = $user->withAccessToken(
            $user->getAccessToken($this->getBearerToken())
        );

        $this->login($user);

        $this->loginModel->recordLoginAttempt('token: ' . ($credentials['token'] ?? ''), true, $ipAddress, $this->user->id);

        return $result;
    }

    /**
     * Checks a user's $credentials to see if they match an
     * existing user.
     *
     * In this case, $credentials has only a single valid value: token,
     * which is the plain text token to return.
     *
     * @param array $credentials
     * @return Result
     */
    public function check(array $credentials)
    {
        if (! array_key_exists('token', $credentials) || empty($credentials['token'])) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.noToken'),
            ]);
        }

        if (strpos($credentials['token'], 'Bearer') === 0) {
            $credentials['token'] = trim(substr($credentials['token'], 6));
        }

        $tokens = model(AccessTokenModel::class);
        $token  = $tokens->where('token', hash('sha256', $credentials['token']))->first();

        if ($token === null) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badToken'),
            ]);
        }

        return new Result([
            'success'   => true,
            'extraInfo' => $token->user(),
        ]);
    }

    /**
     * Checks if the user is currently logged in.
     * Since AccessToken usage is inherently stateless,
     * returns simply whether a user has been
     * authenticated or not.
     */
    public function loggedIn(): bool
    {
        return $this->user instanceof AuthenticatorInterface;
    }

    /**
     * Logs the given user in by saving them to the class.
     * Since AccessTokens are stateless, $remember has no functionality.
     *
     * @return mixed
     */
    public function login(AuthenticatorInterface $user, bool $remember = false)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Logs a user in based on their ID.
     *
     * @return mixed
     */
    public function loginById(int $userId, bool $remember = false)
    {
        $user = $this->provider->findById($userId);

        if (empty($user)) {
            throw AuthenticationException::forInvalidUser();
        }

        $user->withAccessToken(
            $user->getAccessToken($this->getBearerToken())
        );

        return $this->login($user, $remember);
    }

    /**
     * Logs the current user out.
     *
     * @return mixed
     */
    public function logout()
    {
        $this->user = null;
    }

    /**
     * Removes any remember-me tokens, if applicable.
     *
     * @return mixed
     */
    public function forget(?int $id)
    {
        // Nothing to do here...
    }

    /**
     * Returns the currently logged in user.
     *
     * @return AuthenticatorInterface|null
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getBearerToken()
    {
        $request = service('request');

        $header = $request->getHeaderLine('Authorization');

        if (empty($header)) {
            return null;
        }

        return trim(substr($header, 6));
    }
}
