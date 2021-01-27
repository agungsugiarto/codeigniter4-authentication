<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Config\Services;
use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\Mock\MockEvents;
use Fluent\Auth\AuthenticationService;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Entities\AccessToken;
use Fluent\Auth\Entities\User;
use Fluent\Auth\Facades\Auth;
use Fluent\Auth\Models\UserModel;
use Fluent\Auth\Result;

class TokenAdapter extends CIDatabaseTestCase
{
    /** @var AuthenticationService|AuthenticationInterface */
    protected $auth;

    protected $namespace = '\Fluent\Auth';

    /** @var Events */
    protected $events;

    public function setUp(): void
    {
        parent::setUp();

        helper('auth');

        $this->auth = auth('token');

        $this->events = new MockEvents();
        Services::injectMock('events', $this->events);
    }

    public function testLogin()
    {
        $user = fake(UserModel::class);

        $this->auth->login($user);

        // Stores the user
        $this->assertInstanceOf(User::class, $this->auth->getUser());
        $this->assertEquals($user->id, $this->auth->getUser()->id);
    }

    public function testLogout()
    {
        // this one's a little odd since it's stateless, but roll with it...
        $user = fake(UserModel::class);

        $this->auth->login($user);
        $this->assertNotNull($this->auth->getUser());

        $this->auth->logout();
        $this->assertNull($this->auth->getUser());
    }

    public function testLoginByidNoToken()
    {
        $user = fake(UserModel::class);

        $this->assertFalse($this->auth->loggedIn());

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertNull($this->auth->getUser()->currentAccessToken());
    }

    public function testLoginByIdWithToken()
    {
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $this->setRequestHeader($token->raw_token);

        $this->auth->loginById($user->id);

        $this->assertTrue($this->auth->loggedIn());
        $this->assertInstanceOf(AccessToken::class, $this->auth->getUser()->currentAccessToken());
        $this->assertEquals($token->id, $this->auth->getUser()->currentAccessToken()->id);
    }

    public function testCheckNoToken()
    {
        $result = $this->auth->check([]);

        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.noToken'), $result->reason());
    }

    public function testCheckBadToken()
    {
        $result = $this->auth->check(['token' => 'abc123']);

        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.badToken'), $result->reason());
    }

    public function testCheckSuccess()
    {
        $user   = fake(UserModel::class);
        $token  = $user->generateAccessToken('foo');
        $result = $this->auth->check(['token' => $token->raw_token]);

        $this->assertTrue($result->isOK());
        $this->assertInstanceOf(User::class, $result->extraInfo());
        $this->assertEquals($user->id, $result->extraInfo()->id);
    }

    public function testAttemptCannotFindUser()
    {
        $result = $this->auth->attempt([
            'token' => 'abc123',
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertFalse($result->isOK());
        $this->assertEquals(lang('Auth.badToken'), $result->reason());

        // A login attempt should have always been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => 'token: abc123',
            'success' => 0,
        ]);
    }

    public function testAttemptSuccess()
    {
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');
        $this->setRequestHeader($token->raw_token);

        $result = $this->auth->attempt([
            'token' => $token->raw_token,
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertInstanceOf(AccessToken::class, $foundUser->currentAccessToken());
        $this->assertEquals($token->token, $foundUser->currentAccessToken()->token);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => 'token: ' . $token->raw_token,
            'success' => 1,
        ]);
    }

    public function testAuthTokenFacade()
    {
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');
        $this->setRequestHeader($token->raw_token);

        $result = Auth::adapter('token')->attempt([
            'token' => $token->raw_token,
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isOK());

        $foundUser = $result->extraInfo();
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertInstanceOf(AccessToken::class, $foundUser->currentAccessToken());
        $this->assertEquals($token->token, $foundUser->currentAccessToken()->token);

        // A login attempt should have been recorded
        $this->seeInDatabase('auth_logins', [
            'email'   => 'token: ' . $token->raw_token,
            'success' => 1,
        ]);
    }

    protected function setRequestHeader(string $token)
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Bearer ' . $token);
    }
}
