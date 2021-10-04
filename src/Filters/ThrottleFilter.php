<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Facades\Auth;
use Fluent\Auth\Facades\RateLimiter;
use Fluent\Auth\Helpers\Str;

use function array_values;

class ThrottleFilter implements FilterInterface
{
    use ResponseTrait;

    /** @var Response */
    protected $response;

    public function __construct()
    {
        $this->response = Services::response();
    }

    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($this->tooManyAttempts($request, $arguments)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request));

            if ($request->isAJAX()) {
                return $this->fail(lang('Auth.throttle', [$seconds]));
            }

            return redirect()->back()->with('error', lang('Auth.throttler', [$this->maxAttempt($arguments), $seconds]));
        }

        RateLimiter::hit($this->throttleKey($request), $this->decaySecond($arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @return bool
     */
    protected function tooManyAttempts(RequestInterface $request, array $arguments)
    {
        return RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempt($arguments));
    }

    /**
     * Get the number of attempts for the given key.
     *
     * @return int
     */
    protected function maxAttempt(array $arguments)
    {
        return (int) array_values($arguments)[1];
    }

    /**
     * Get the decay second for the given key.
     *
     * @return int
     */
    protected function decaySecond(array $arguments)
    {
        return (int) array_values($arguments)[0];
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(RequestInterface $request)
    {
        return 'throttle_' . Str::extractName(Auth::user()->email) . '_' . str_replace("::", "", $request->getIPAddress());
    }
}
