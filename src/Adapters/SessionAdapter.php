<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\App;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Models\LoginModel;
use Fluent\Auth\Models\RememberModel;
use Fluent\Auth\Result;

use function bin2hex;
use function count;
use function date;
use function hash;
use function is_null;
use function mt_rand;
use function random_bytes;
use function time;

class SessionAdapter implements AuthenticationInterface
{
    /** @var Auth */
    protected $config;

    /** @var UserProviderInterface */
    protected $provider;

    /** @var AuthenticatorInterface|User */
    protected $user;

    /** @var LoginModel */
    protected $loginModel;

    /** @var RememberModel */
    protected $rememberModel;

    /** @var IncomingRequest */
    protected $request;

    /** @var Response */
    protected $response;

    /**
     * Session adapter constructor.
     */
    public function __construct($config, UserProviderInterface $provider)
    {
        $this->config        = $config;
        $this->provider      = $provider;
        $this->loginModel    = new LoginModel();
        $this->rememberModel = new RememberModel();
        $this->request       = service('request');
        $this->response      = service('response');
    }

    /**
     * {@inheritdoc}
     */
    public function attempt($credentials, bool $remember = false): Result
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

        $this->login($result->extraInfo(), $remember);

        $this->loginModel->recordLoginAttempt($credentials['email'] ?? $credentials['username'], true, $ipAddress, $this->user->id ?? null);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function check(array $credentials): Result
    {
        // Can't validate without a password.
        if (empty($credentials['password']) || count($credentials) < 2) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.badAttempt'),
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
        $passwords = service('passwords');

        if (! $passwords->verify($givenPassword, $user->password)) {
            return new Result([
                'success' => false,
                'reason'  => lang('Auth.invalidPassword'),
            ]);
        }

        // Check to see if the password needs to be rehashed.
        // This would be due to the hash algorithm or hash
        // cost changing since the last time that a user
        // logged in.
        if ($passwords->needsRehash($user->password)) {
            $user->password = $passwords->hash($givenPassword);
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
        if ($this->user instanceof AuthenticatorInterface) {
            return true;
        }

        if ($userId = session($this->config->sessionConfig['field'])) {
            $this->user = $this->provider->findById($userId);

            return $this->user instanceof AuthenticatorInterface;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function login(AuthenticatorInterface $user, bool $remember = false): bool
    {
        $this->user = $user;

        // Always record a login attempt
        $ipAddress = $this->request->getIPAddress();
        $this->recordLoginAttempt($user->getAuthEmail(), true, $ipAddress, $user->getAuthId() ?? null);

        // Regenerate the session ID to help protect against session fixation
        if (ENVIRONMENT !== 'testing') {
            session()->regenerate();
        }

        // Let the session know we're logged in
        session()->set($this->config->sessionConfig['field'], $this->user->id);

        // When logged in, ensure cache control headers are in place
        $this->response->noCache();

        if ($remember && $this->config->sessionConfig['allowRemembering']) {
            $this->rememberUser($this->user->id);
        }

        // We'll give a 20% chance to need to do a purge since we
        // don't need to purge THAT often, it's just a maintenance issue.
        // to keep the table from getting out of control.
        if (mt_rand(1, 100) <= 20) {
            $this->rememberModel->purgeOldRememberTokens();
        }

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

        if (empty($user)) {
            throw AuthenticationException::forInvalidUser();
        }

        return $this->login($user, $remember);
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        // Destroy the session data - but ensure a session is still
        // available for flash messages, etc.
        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                $_SESSION[$key] = null;
                unset($_SESSION[$key]);
            }
        }

        // Regenerate the session ID for a touch of added safety.
        session()->regenerate(true);

        // Take care of any remember me functionality
        $this->rememberModel->purgeRememberTokens($this->user->id ?? null);

        // trigger logout event
        Events::trigger('logout', $this->user);

        $this->user = null;
    }

    /**
     * {@inheritdoc}
     */
    public function forget(?int $id)
    {
        if (empty($id)) {
            if (! $this->loggedIn()) {
                return;
            }

            $id = $this->user->id;
        }

        $this->rememberModel->purgeRememberTokens($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Record a login attempt.
     *
     * @return boolean|integer|string
     */
    protected function recordLoginAttempt(string $email, bool $success, ?string $ipAddress = null, ?int $userID = null)
    {
        return $this->loginModel->insert([
            'ip_address' => $ipAddress,
            'email'      => $email,
            'user_id'    => $userID,
            'date'       => date('Y-m-d H:i:s'),
            'success'    => (int) $success,
        ]);
    }

    /**
     * Generates a timing-attack safe remember me token
     * and stores the necessary info in the db and a cookie.
     *
     * @see https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
     *
     * @throws Exception
     */
    protected function rememberUser(int $userID)
    {
        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(20));
        $expires   = date('Y-m-d H:i:s', time() + $this->config->sessionConfig['rememberLength']);

        $token = $selector . ':' . $validator;

        // Store it in the database
        $this->rememberModel->rememberUser($userID, $selector, hash('sha256', $validator), $expires);

        // Save it to the user's browser in a cookie.
        $appConfig = new App();

        // Create the cookie
        $this->response->setCookie(
            $this->config->sessionConfig['rememberCookieName'],
            $token, // Value
            $this->config->sessionConfig['rememberLength'], // # Seconds until it expires
            $appConfig->cookieDomain,
            $appConfig->cookiePath,
            $appConfig->cookiePrefix,
            false, // Only send over HTTPS?
            true // Hide from Javascript?
        );
    }
}
