<?php

namespace Fluent\Auth\Filters;

use Fluent\Auth\Config\Services;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Authorize implements FilterInterface
{
    /**
     * The gate instance.
     *
     * @var \Fluent\Auth\Contracts\GateInterface
     */
    protected $gate;

    /**
     * Create a new filter instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->gate = Services::gate();
    }

    /**
     * {@inheritdoc}
     * 
     * @throws \Fluent\Auth\Exceptions\AuthenticationException
     * @throws \Fluent\Auth\Exceptions\AuthorizationException
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        [$ability] = $arguments;

        $this->gate->authorize($ability, $arguments);

        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    { 
    }
}
