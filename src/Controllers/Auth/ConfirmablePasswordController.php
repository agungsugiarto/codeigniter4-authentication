<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\View\RendererInterface;
use Fluent\Auth\Facades\Auth;

use function auth;
use function time;

class ConfirmablePasswordController extends BaseController
{
    /**
     * Show the confirm password view.
     *
     * @return RendererInterface
     */
    public function show()
    {
        return view('Auth\confirm_password');
    }

    /**
     * Confirm the users password.
     *
     * @return mixed
     */
    public function create()
    {
        $request = (object) $this->request->getPost();

        if (
            ! Auth::validate([
                'email'    => auth()->user()->email,
                'password' => $request->password,
            ])
        ) {
            return redirect()->back()->with('error', lang('Passwords.confirm'));
        }

        session()->set('password_confirmed_at', time());

        return redirect()->route('dashboard');
    }
}
