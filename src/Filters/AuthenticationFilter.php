<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

class AuthenticationFilter implements FilterInterface
{
    /**
     * @var AuthFactoryInterface|AuthenticationInterface
     */
    protected $auth;

    public function __construct()
    {
        $this->auth = Services::auth();
    }

    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            $arguments = [null];
        }

        foreach ($arguments as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        throw new AuthenticationException('Unauthenticated', ResponseInterface::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}