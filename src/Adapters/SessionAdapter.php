<?php

namespace Fluent\Auth\Adapters;

use Codeigniter\Config\Services;
use Codeigniter\Encryption\EncrypterInterface;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;
use Exception;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\CookieRecaller;
use Fluent\Auth\Traits\GuardHelperTrait;

use function bin2hex;
use function is_null;
use function random_bytes;
use function sha1;

class SessionAdapter implements AuthenticationInterface
{
    use GuardHelperTrait;

    /** @var AuthenticatorInterface */
    protected $lastAttempted;

    /** @var boolean */
    protected $loggedOut = false;

    /** @var boolean */
    protected $viaRemember = false;

    /** @var boolean */
    protected $recallAttempted = false;

    /** @var string */
    protected $name;

    /** @var RequestInterface */
    protected $request;

    /** @var ResponseInterface */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /**
     * Create new session adapter guard.
     *
     * @return void
     */
    public function __construct(
        string $name,
        UserProviderInterface $provider,
        RequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session
    ) {
        $this->name     = $name;
        $this->provider = $provider;
        $this->request  = $request;
        $this->response = $response;
        $this->session  = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionName()
    {
        return 'login_' . "{$this->name}_" . sha1(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieName()
    {
        return 'remember_' . "{$this->name}_" . sha1(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function viaRemember()
    {
        return $this->viaRemember;
    }

    /**
     * {@inheritdoc}
     */
    public function attempt(array $credentials, bool $remember = false)
    {
        Events::trigger('fireAttemptEvent', $credentials, $remember);

        $this->lastAttempted = $user = $this->provider->findByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

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
        $this->lastAttempted = $user = $this->provider->findByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function login(AuthenticatorInterface $user, bool $remember = false)
    {
        $this->updateSession($user->getAuthId());

        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->recallerCookie($user);
        }

        Events::trigger('fireLoginEvent', $user, $remember);

        // Provide codeigniter4/authentitication-implementation
        Events::trigger('login', $user, $remember);

        $this->setUser($user);
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

        $this->clearUserDataFromStorage();

        if (! is_null($this->user) && ! empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }

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

        $id = $this->session->get($this->getSessionName());

        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        if (! is_null($id) && $this->user = $this->provider->findById($id)) {
            Events::trigger('fireAuthenticatedEvent', $this->user);
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        if (is_null($this->user) && ! is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthId());
                Events::trigger('fireLoginEvent', $this->user, true);
            }
        }

        return $this->user;
    }

    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @return void
     */
    protected function recallerCookie(AuthenticatorInterface $user)
    {
        $app = config('App');

        // If using login with remember, make sure to send cookie with redirect()->withCookies()
        $this->response->setCookie(
            $this->getCookieName(),
            $this->encrypter()->encrypt($user->getAuthId() . '|' . $user->getRememberToken() . '|' . $user->getAuthPassword()),
            1 * MONTH,
            $app->cookieDomain,
            $app->cookiePath,
            $app->cookiePrefix,
            $app->cookieSecure,
            $app->cookieHTTPOnly
        );
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @return CookieRecaller|null
     */
    protected function recaller()
    {
        if ($recaller = $this->request->getCookie($this->getCookieName())) {
            try {
                $decrypted = $this->encrypter()->decrypt($recaller);
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                return null;
            }

            return new CookieRecaller($decrypted);
        }

        return null;
    }

    /**
     * Get service instance encrypter.
     *
     * @return EncrypterInterface
     */
    protected function encrypter()
    {
        return Services::encrypter();
    }

    /**
     * Pull a user from the repository by its "remember me" cookie token.
     *
     * @param CookieRecaller $recaller
     * @return mixed
     */
    protected function userFromRecaller($recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return;
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $this->recallAttempted = true;

        $this->viaRemember = ! is_null($user = $this->provider->findByRememberToken(
            $recaller->id(),
            $recaller->token()
        ));

        return $user;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        $validated = ! is_null($user) && $this->provider->validateCredentials($user, $credentials);

        if ($validated) {
            Events::trigger('fireValidatedEvent', $user);
        }

        return $validated;
    }

    /**
     * Update the session with the given ID.
     *
     * @param  string  $id
     * @return void
     */
    protected function updateSession($id)
    {
        $this->session->set($this->getSessionName(), $id);

        $this->session->regenerate(true);
    }

    /**
     * Create a new "remember me" token for the user if one doesn't already exist.
     *
     * @return void
     */
    protected function ensureRememberTokenIsSet(AuthenticatorInterface $user)
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }

    /**
     * Refresh the "remember me" token for the user.
     *
     * @return void
     */
    protected function cycleRememberToken(AuthenticatorInterface $user)
    {
        $user->setRememberToken($token = bin2hex(random_bytes(20)));

        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * Remove the user data from the session and cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        $app = config('App');
        $this->session->remove($this->getSessionName());

        if (! is_null($this->recaller())) {
            // If using login with remember, make sure to send cookie with redirect()->withCookies()
            $this->response->deleteCookie(
                $this->getCookieName(),
                $app->cookieDomain,
                $app->cookiePath,
                $app->cookiePrefix
            );
        }
    }
}
