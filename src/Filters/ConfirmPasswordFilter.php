<?php

namespace Fluent\Auth\Filters;

use CodeIgniter\Config\Services;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use function time;

class ConfirmPasswordFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($this->shouldConfirmPassword()) {
            if ($request->isAjax()) {
                return Services::response()->setJSON([
                    'message' => 'Password confirmation required.',
                ])->setStatusCode(ResponseInterface::HTTP_LOCKED);
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
