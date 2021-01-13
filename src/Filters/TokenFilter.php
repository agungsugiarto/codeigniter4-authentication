<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Facades\Auth;

class TokenFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $result = Auth::adapter('token')->check([
            'token' => $request->getHeaderLine('Authorization'),
        ]);

        if (! $result->isOK()) {
            return Services::response()->setJSON([
                'success' => false,
                'message' => $result->reason(),
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
