<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Models\LoginModel;
use Fluent\Auth\Result;

use function count;
use function is_null;
use function substr;
use function trim;

class JwtAdapter implements AuthenticationInterface
{
    /** @var Auth */
    protected $config;

    /** @var UserProviderInterface */
    protected $provider;

    /** @var AuthenticatorInterface */
    protected $user;

    /** @var LoginModel */
    protected $loginModel;

    /** @var IncomingRequest */
    protected $request;

    /**
     * Token adapter constructor.
     */
    public function __construct(Auth $config, UserProviderInterface $provider)
    {
        $this->config     = $config;
        $this->provider   = $provider;
        $this->loginModel = new LoginModel();
        $this->request    = Services::request();
    }

    /**
     * {@inheritdoc}
     */
    public function attempt(array $credentials, bool $remember = false)
    {
        $ipAddress = $this->request->getIPAddress();
        $result    = $this->check($credentials);

        if (! $result->isOK()) {
            // Always record a login attempt, whether success or not.
            $this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], false, $ipAddress, null);

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

        $this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], false, $ipAddress, null);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function check(array $credentials)
    {
        // Can't validate without a password.
        if (empty($credentials['password']) || count($credentials) < 2) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.noToken'),
            ]);
        }

        // Remove the password from credentials so we can
        // check afterword.
        $givenPassword = $credentials['password'] ?? null;
        unset($credentials['password']);

        // Find the existing user
        $user = $this->provider->findByCredentials($credentials);

        if (is_null($user)) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badAttempt'),
            ]);
        }

        // Now, try matching the passwords.
        $passwords = service('password');
        if (! $passwords->verify($givenPassword, $user->password_hash)) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.invalidPassword'),
            ]);
        }

        // Check to see if the password needs to be rehashed.
        // This would be due to the hash algorithm or hash
        // cost changing since the last time that a user
        // logged in.
        if ($passwords->needsRehash($user->password_hash)) {
            $user->password_hash = $passwords->hash($givenPassword);
            $this->provider->save($user);
        }

        return new Result([
            'success'   => true,
            'extraInfo' => $user,
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
    public function login(AuthenticatorInterface $user, bool $remember = false)
    {
        $this->user = $user;

        // trigger login event, in case anyone cares
        Events::trigger('login', $user);

        return $this;
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
