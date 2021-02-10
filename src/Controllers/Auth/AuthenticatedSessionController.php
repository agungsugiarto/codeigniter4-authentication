<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\Http\RedirectResponse;
use CodeIgniter\View\RendererInterface;
use Fluent\Auth\Facades\Auth;

use function func_get_args;
use function is_array;
use function is_bool;
use function trim;

class AuthenticatedSessionController extends BaseController
{
    /**
     * Display the login view.
     *
     * @return RendererInterface
     */
    public function new()
    {
        return view('Auth\login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        $request = (object) $this->request->getPost();

        $credentials = ['email' => $request->email, 'password' => $request->password];
        $remember    = $this->filled('remember');

        if (! $this->validate(['email' => 'required|valid_email', 'password' => 'required'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! Auth::attempt($credentials, $remember)) {
            return redirect()->back()->withInput()->with('error', lang('Auth.failed'));
        }

        return redirect('dashboard')->withCookies();
    }

    /**
     * Destroy an authenticated session.
     *
     * @return RedirectResponse
     */
    public function delete()
    {
        Auth::logout();

        return redirect('/')->withCookies();
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param  string|array  $key
     * @return bool
     */
    protected function filled($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param  string  $key
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $value = $this->request->getVar($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }
}
