<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Exceptions\AuthenticationException;

use function auth;

class EmailVerifiedFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();

        if (
            ! $user ||
            ($user instanceof VerifyEmailInterface &&
            ! $user->hasVerifiedEmail())
        ) {
            if ($request->isAjax()) {
                throw new AuthenticationException('Your email address is not verified', ResponseInterface::HTTP_FORBIDDEN);
            }

            return redirect()->route($arguments[0] ?: 'verification.notice')->with('error', 'Your email address is not verified');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
