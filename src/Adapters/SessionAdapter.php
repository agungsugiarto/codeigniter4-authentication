<?php

namespace Fluent\Auth\Adapters;

use CodeIgniter\Events\Events;
use Config\App;
use Fluent\Auth\Contracts\AuthenticatorInterface;

use function bin2hex;
use function is_null;
use function random_bytes;

class SessionAdapter extends AbstractAdapter
{
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
    public function login(AuthenticatorInterface $user, bool $remember = false): void
    {
        $this->updateSession($user->getAuthId());

        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->rememberUser($user);
        }

        Events::trigger('fireLoginEvent', $user, $remember);

        $this->setUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loginById(int $userId, bool $remember = false)
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

        $id = $this->session->get($this->config->sessionConfig['field']);

        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        if (! is_null($id) && $this->user = $this->provider->findById((int) $id)) {
            Events::trigger('fireAuthenticatedEvent', $this->user);
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        if (is_null($this->user) && ! is_null($recaller = $this->request->getCookie($this->config->sessionConfig['rememberCookieName']))) {
            $this->user = $this->provider->findByRememberToken((int) $id, $recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthId());
                Events::trigger('fireLoginEvent', $this->user, true);
            }
        }

        return $this->user;
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
        $this->session->set($this->config->sessionConfig['field'], $id);

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
        $this->session->remove($this->config->sessionConfig['field']);

        $this->response->deleteCookie($this->config->sessionConfig['rememberCookieName']);
    }

    /**
     * Generates a timing-attack safe remember me token
     * and stores the necessary info in the db and a cookie.
     *
     * @throws Exception
     */
    protected function rememberUser(AuthenticatorInterface $user)
    {
        // Save it to the user's browser in a cookie.
        $appConfig = new App();

        // Create the cookie
        $this->response->setCookie(
            $this->config->sessionConfig['rememberCookieName'],
            $user->getRememberToken(), // Value
            $this->config->sessionConfig['rememberLength'], // # Seconds until it expires
            $appConfig->cookieDomain,
            $appConfig->cookiePath,
            $appConfig->cookiePrefix,
            false, // Only send over HTTPS?
            true // Hide from Javascript?
        );
    }
}
