<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\Http\RedirectResponse;
use CodeIgniter\View\RendererInterface;
use Fluent\Auth\Facades\Auth;
use Fluent\Auth\Facades\RateLimiter;

use function func_get_args;
use function is_array;
use function is_bool;
use function strtolower;
use function trim;

class AuthenticatedSessionController extends BaseController
{
    /**
     * Max attempt login throttle.
     *
     * @var int
     */
    const MAX_ATTEMPT = 5;

    /**
     * Decay in second if failed attempt.
     *
     * @var int
     */
    const DECAY_SECOND = 60;

    /**
     * Display the login view.
     *
     * @return RendererInterface
     */
    public function new()
    {
        return view('Auth\login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        // Populate request to object.
        $request = (object) $this->request->getPost();

        // Credentials for attempt login.
        $credentials = ['email' => $request->email, 'password' => $request->password];

        // Credential if remember.
        $remember = $this->filled('remember');

        // Rate limiter how many can be attempt.
        if (RateLimiter::tooManyAttempts($this->throttleKey(), static::MAX_ATTEMPT)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

            return redirect()->back()->withInput()->with('error', lang('Auth.throttle', [$seconds]));
        }

        // Validate this credentials request.
        if (! $this->validate(['email' => 'required|valid_email', 'password' => 'required'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Try to login this credentials.
        if (! Auth::attempt($credentials, $remember)) {
            // Save throttle state.
            RateLimiter::hit($this->throttleKey(), static::DECAY_SECOND);

            return redirect()->back()->withInput()->with('error', lang('Auth.failed'));
        }

        // Clear the throttle key
        RateLimiter::clear($this->throttleKey());

        // Finnaly we're success login.
        return redirect(config('Auth')->home)->withCookies();
    }

    /**
     * Destroy an authenticated session.
     *
     * @return RedirectResponse
     */
    public function delete()
    {
        Auth::logout();

        return redirect('/')->withCookies();
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    protected function filled($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $value = $this->request->getVar($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @param object $request
     * @return string
     */
    public function throttleKey()
    {
        return strtolower($this->request->getPost('email')) . '_' . $this->request->getIPAddress();
    }
}
