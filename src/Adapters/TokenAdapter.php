<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Models\AccessTokenModel;
use Fluent\Auth\Models\LoginModel;
use Fluent\Auth\Result;

use function array_key_exists;
use function hash;
use function is_null;
use function strpos;
use function substr;
use function trim;

class TokenAdapter implements AuthenticationInterface
{
    /** @var Auth */
    protected $config;

    /** @var UserProviderInterface */
    protected $provider;

    /** @var AuthenticatorInterface|User */
    protected $user;

    /** @var LoginModel */
    protected $loginModel;

    /** @var IncomingRequest */
    protected $request;

    /**
     * Token adapter constructor.
     */
    public function __construct($config, UserProviderInterface $provider)
    {
        $this->config     = $config;
        $this->provider   = $provider;
        $this->loginModel = new LoginModel();
        $this->request    = Services::request();
    }

    /**
     * {@inheritdoc}
     */
    public function attempt(array $credentials, bool $remember = false): Result
    {
        $ipAddress = $this->request->getIPAddress();
        $result    = $this->check($credentials);

        if (! $result->isOK()) {
            // Always record a login attempt, whether success or not.
            $this->loginModel->recordLoginAttempt('token: ' . ($credentials['token'] ?? ''), false, $ipAddress, null);

            $this->user = null;

            // Fire an event on failure so devs have the chance to
            // let them know someone attempted to login to their account
            Events::trigger('failed_login_attempt', $credentials);

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
     * {@inheritdoc}
     */
    public function check(array $credentials): Result
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

        $tokens = new AccessTokenModel();
        $token  = $tokens->where('token', hash('sha256', $credentials['token']))->first();

        if (is_null($token)) {
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
     * {@inheritdoc}
     */
    public function loggedIn(): bool
    {
        return $this->user instanceof AuthenticatorInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function login(AuthenticatorInterface $user, bool $remember = false): bool
    {
        $this->user = $user;

        // trigger login event, in case anyone cares
        Events::trigger('login', $user);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function loginById(int $userId, bool $remember = false)
    {
        $user = $this->provider->findById($userId);

        if (is_null($user)) {
            throw AuthenticationException::forInvalidUser();
        }

        $user->withAccessToken(
            $user->getAccessToken($this->getBearerToken())
        );

        return $this->login($user, $remember);
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        // trigger logout event
        Events::trigger('logout', $this->user);

        $this->user = null;
    }

    /**
     * {@inheritdoc}
     */
    public function forget(?int $id)
    {
        // Nothing to do here...
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getBearerToken()
    {
        if (empty($header = $this->request->getHeaderLine('Authorization'))) {
            return null;
        }

        return trim(substr($header, 6));
    }
}
