<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

use function auth;

class EmailVerificationPromptController extends BaseController
{
    /**
     * Display the email verification prompt.
     *
     * @return mixed
     */
    public function new()
    {
        return auth()->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('Auth/verify_email');
    }
}
