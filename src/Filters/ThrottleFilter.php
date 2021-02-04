<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Throttle\Throttler;
use Fluent\Auth\Config\Auth as Config;
use Fluent\Auth\Facades\Auth;

use function sha1;

class ThrottleFilter implements FilterInterface
{
    use ResponseTrait;

    /** @var Throttler */
    protected $throttler;

    /** @var Response */
    protected $response;

    /** @var Config */
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
        if ($this->throttler->check($this->key($request), $this->config->passwords['throttle'], MINUTE) === false) {
            if ($request->isAJAX()) {
                return $this->fail(lang('Auth.throttler', [$this->config->passwords['throttle']]));
            }

            return redirect()->back()->with('error', lang('Auth.throttler', [$this->config->passwords['throttle']]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * Generate signature key for throttle.
     *
     * @return mixed
     */
    protected function key(RequestInterface $request)
    {
        return sha1($request->getIPAddress() . '|' . Auth::user()->getAuthId());
    }
}
