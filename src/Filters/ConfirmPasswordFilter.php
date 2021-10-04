<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use function time;
class ConfirmPasswordFilter implements FilterInterface
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
        if ($this->shouldConfirmPassword()) {
            if ($request->isAjax()) {
                return $this->fail('Password confirmation required.', ResponseInterface::HTTP_LOCKED);
            }

            session()->set('intended', current_url());

            return redirect()->route('password.confirm')->with('error', 'Password confirmation required.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * Determine if the confirmation timeout has expired.
     *
     * @return bool
     */
    protected function shouldConfirmPassword()
    {
        $confirmedAt = time() - session('password_confirmed_at');

        return $confirmedAt > config('Auth')->passwordTimeout;
    }
}
