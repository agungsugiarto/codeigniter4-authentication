<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;

use function auth;

class EmailVerifiedFilter implements FilterInterface
{
    use ResponseTrait;

    /** @var ResponseInterface */
    protected $response;

    public function __construct()
    {
        $this->response = Services::response();
    }

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
                return $this->fail('Your email address is not verified', ResponseInterface::HTTP_FORBIDDEN);
            }

            session()->set('intended', current_url());

            return redirect()->route('verification.notice')->with('error', 'Your email address is not verified');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
