<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Mock\MockEvents;
use Fluent\Auth\AuthenticationFactory;
use Fluent\Auth\AuthenticationService;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Facades\Auth as FacadesAuth;
use Fluent\Auth\Models\RememberModel;
use Fluent\Auth\Models\UserModel;
use Fluent\Auth\Result;

class SessionAdapterTest extends CIDatabaseTestCase
{
    /** @var AuthenticationService|AuthenticationInterface */
    protected $auth;

    protected $namespace = '\Fluent\Auth';

    /** @var Events */
    protected $events;

    public function setUp(): void
    {
        parent::setUp();

        $this->auth = new AuthenticationService(new AuthenticationFactory(new Auth()));

        $this->events = new MockEvents();
        Services::injectMock('events', $this->events);
    }

    public function testLoggedIn()
    {
        $this->assertFalse($this->auth->loggedIn());

        $user = fake(UserModel::class);
        session()->set('logged_in', $user->id);

        $this->assertTrue($this->auth->loggedIn());

        $authUser = $this->auth->user();
        $this->assertInstanceOf(User::class, $authUser);
        $this->assertEquals($user->id, $authUser->id);
    }

    public function testLoginNoRemember()
    {
        $user = fake(UserModel::class);

        $this->assertTrue($this->auth->login($user, false));
        $this->assertEquals($user->id, session()->get('logged_in'));

        // Login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'success' => true,
        ]);

        $this->dontSeeInDatabase('auth_remember_tokens', [
            'user_id' => $user->id,
        ]);
    }

    public function testLoginWithRemember()
    {
        $user = fake(UserModel::class);

        $this->assertTrue($this->auth->login($user, true));

        $this->assertEquals($user->id, session()->get('logged_in'));

        // Login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'success' => true,
        ]);

        $this->seeInDatabase('auth_remember_tokens', [
            'user_id' => $user->id,
        ]);

        // Cookie should have been set
        $response = service('response');
        $this->assertNotNull($response->getCookie('remember'));
    }

    public function testLogout()
    {
        $user = fake(UserModel::class);
        $this->auth->login($user, true);

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);

        $this->auth->logout();

        $this->assertFalse(session()->has('logged_in'));
        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
    }

    public function testLoginByIdBadUser()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.invalidUser'));

        $this->auth->loginById(123);
    }

    public function testLoginById()
    {
        $user = fake(UserModel::class);

        $this->auth->loginById($user->id);

        $this->assertEquals($user->id, session()->get('logged_in'));

        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
    }

    public function testLoginByIdRemember()
    {
        $user = fake(UserModel::class);

        $this->auth->loginById($user->id, true);

        $this->assertEquals($user->id, session()->get('logged_in'));

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
    }

    public function testForgetCurrentUser()
    {
        $user = fake(UserModel::class);
        $this->auth->loginById($user->id, true);
        $this->assertEquals($user->id, session()->get('logged_in'));

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);

        $this->auth->forget(null);

        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
    }

    public function testForgetAnotherUser()
    {
        $user = fake(UserModel::class);
        fake(RememberModel::class, ['user_id' => $user->id]);

        $this->seeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);

        $this->auth->forget($user->id);

        $this->dontSeeInDatabase('auth_remember_tokens', ['user_id' => $user->id]);
    }

    public function testCheckNoPassword()
    {
        $result = $this->auth->check([
            'email' => 'johnsmith@example.com',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.badAttempt'), $result->reason());
    }

    public function testCheckCannotFindUser()
    {
        $result = $this->auth->check([
            'email'    => 'johnsmith@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.badAttempt'), $result->reason());
    }

    public function testCheckBadPassword()
    {
        $user = fake(UserModel::class, [
            'password_hash' => 'passwords',
        ]);

        $result = $this->auth->check([
            'email'    => $user->email,
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.invalidPassword'), $result->reason());
    }

    public function testCheckSuccess()
    {
        $user = fake(UserModel::class, [
            'password_hash' => 'passwords',
        ]);

        $result = $this->auth->check([
            'email'    => $user->email,
            'password' => 'passwords',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertEquals($user, $foundUser);
    }

    public function testAttemptCanotFindUser()
    {
        $result = $this->auth->attempt([
            'email'    => 'johnsmith@example.com',
            'password' => 'secret',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.badAttempt'), $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => 'johnsmith@example.com',
            'success' => 0,
        ]);
    }

    public function testAttemptSuccess()
    {
        $user = fake(UserModel::class, [
            'password_hash' => 'passwords',
        ]);

        $this->assertFalse(session()->has('logged_in'));

        $result = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'passwords',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertEquals($user, $foundUser);

        $this->assertTrue(session()->has('logged_in'));
        $this->assertEquals($user->id, session()->get('logged_in'));

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => $user->email,
            'success' => 1,
        ]);
    }

    public function testAuthSessionFacade()
    {
        $user = fake(UserModel::class, [
            'password_hash' => 'password'
        ]);

        $this->assertFalse(session()->has('logged_in'));

        $result = FacadesAuth::attempt([
            'email'    => $user->email,
            'password' => 'password'
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertEquals($user, $foundUser);

        $this->assertTrue(session()->has('logged_in'));
        $this->assertEquals($user->id, session()->get('logged_in'));

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => $user->email,
            'success' => 1,
        ]);
    }
}
