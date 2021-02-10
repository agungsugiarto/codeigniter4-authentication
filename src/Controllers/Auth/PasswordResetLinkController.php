<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\View\RendererInterface;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Facades\Passwords;

class PasswordResetLinkController extends BaseController
{
    /**
     * Display the password reset link request view.
     *
     * @return RendererInterface
     */
    public function new()
    {
        return view('Auth/forgot_password');
    }

    /**
     * Handle an incomming password reset link request.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        $request = (object) $this->request->getPost();

        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Passwords::sendResetLink([
            'email' => $request->email,
        ]);

        return $status === PasswordBrokerInterface::RESET_LINK_SENT
            ? redirect()->back()->with('message', lang($status))
            : redirect()->back()->withInput()->with('error', lang($status));
    }
}
