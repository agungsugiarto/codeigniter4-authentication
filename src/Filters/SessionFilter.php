<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use function auth;

class SessionFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! auth('session')->check()) {
            throw PageNotFoundException::forPageNotFound(lang('Auth.badAttempt'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
