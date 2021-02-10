<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\View\RendererInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Facades\Auth;
use Fluent\Auth\Models\UserModel;

class RegisteredUserController extends BaseController
{
    /**
     * Display the registration view.
     *
     * @return RendererInterface
     */
    public function new()
    {
        return view('Auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @return RedirectResponse
     */
    public function create()
    {
        $request = (object) $this->request->getPost();

        if (! $this->validate(static::rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = static::createUser($request);

        Auth::login($user);

        // Automatic trigger send verify email if user
        // instanceof VerifyEmailInterface.
        if ($user instanceof VerifyEmailInterface) {
            Events::trigger(VerifyEmailInterface::class, $user->email);
        }

        return redirect('dashboard');
    }

    /**
     * Rules for validation.
     *
     * @return array
     */
    protected static function rules()
    {
        return [
            'username'              => 'required|is_unique[users.username]',
            'email'                 => 'required|valid_email|is_unique[users.email]',
            'password'              => 'required|min_length[8]',
            'password_confirmation' => 'matches[password]',
        ];
    }

    /**
     * Generate user registration.
     *
     * @param object $request
     * @return AuthenticatorInterface
     */
    protected static function createUser($request)
    {
        $user = new UserModel();
        $id   = $user->insert(new User([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password,
        ]));

        return $user->find($id);
    }
}
