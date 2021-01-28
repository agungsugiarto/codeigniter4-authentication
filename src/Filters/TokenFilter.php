<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Facades\Auth;

use function preg_replace;
use function trim;

class TokenFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $result = Auth::adapter('token')->attempt([
            'token' => $this->getTokenForRequest($request),
        ]);

        if (! $result) {
            return Services::response()->setJSON([
                'code'    => ResponseInterface::HTTP_UNAUTHORIZED,
                'message' => lang('Auth.badToken'),
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    protected function getTokenForRequest(RequestInterface $request)
    {
        $token = $request->getVar('token');

        if (empty($token)) {
            $token = $this->bearerToken($request);
        }

        return $token;
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    protected function bearerToken(RequestInterface $request)
    {
        if (empty($header = $request->getHeaderLine('Authorization'))) {
            return null;
        }

        return trim((string) preg_replace('/^(?:\s+)?Token\s/', '', $header)) ?? null;
    }
}
