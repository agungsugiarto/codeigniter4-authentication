<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Facades\Auth;

class RedirectAuthenticatedFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty($arguments)) {
            $arguments = [null];
        }

        foreach ($arguments as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(config('Auth')->home);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
