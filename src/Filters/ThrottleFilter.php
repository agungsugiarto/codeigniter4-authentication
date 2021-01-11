<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Throttle\Throttler;
use Fluent\Auth\Config\Auth;

class ThrottleFilter implements FilterInterface
{
    use ResponseTrait;

    /** @var Throttler */
    protected $throttler;

    /** @var Response */
    protected $response;

    /** @var Auth */
    protected $config;

    public function __construct()
    {
        $this->config    = config('Auth');
        $this->throttler = Services::throttler();
        $this->response  = Services::response();
    }

    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($this->throttler->check($request->getIPAddress(), $this->config->throttler, MINUTE) === false) {
            if ($request->isAJAX()) {
                return $this->fail(lang('Auth.throttler', [$this->config->throttler]));
            }

            return redirect()->back()->with('error', lang('Auth.throttler', [$this->config->throttler]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
