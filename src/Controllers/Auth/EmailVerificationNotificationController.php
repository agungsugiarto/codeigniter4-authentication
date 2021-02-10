<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use Fluent\Auth\Contracts\PasswordBrokerInterface;
use Fluent\Auth\Facades\Passwords;

use function auth;

class EmailVerificationNotificationController extends BaseController
{
    /**
     * Send a new verification notification.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $status = Passwords::sendVerifyLink([
            'email' => $user->email,
        ]);

        return $status === PasswordBrokerInterface::VERIFY_LINK_SENT
            ? redirect()->back()->with('message', lang($status))
            : redirect()->back()->with('error', lang($status));
    }
}
