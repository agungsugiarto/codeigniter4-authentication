<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationBasicInterface;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;

class AuthenticationBasicFilter implements FilterInterface
{
    /** @var AuthFactoryInterface|AuthenticationBasicInterface|AuthenticationInterface */
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
        [$guard, $field, $method] = $arguments;

        $this->auth->guard($guard)->{$method}($field);
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}