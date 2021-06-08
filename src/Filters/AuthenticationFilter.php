<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

class AuthenticationFilter implements FilterInterface
{
    use ResponseTrait;

    /** @var AuthFactoryInterface|AuthenticationInterface */
    protected $auth;

    /** @var ResponseInterface */
    protected $response;

    public function __construct()
    {
        $this->auth = Services::auth();
        $this->response = Services::response();
    }

    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        return $this->authenticate($request, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param RequestInterface $request
     * @param array $guards
     * @return void
     * @throws AuthenticationException
     */
    protected function authenticate($request, $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        return $this->unauthenticated($request, $guards);
    }

     /**
      * Handle an unauthenticated user.
      *
      * @param RequestInterface $request
      * @param array $guards
      * @return void
      * @throws AuthenticationException
      */
    protected function unauthenticated($request, $guards)
    {
        if ($request->isAJAX()) {
            return $this->fail('Unauthenticated.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            ResponseInterface::HTTP_UNAUTHORIZED,
            $this->redirectTo($request)
        );
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param RequestInterface $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
    }
}
