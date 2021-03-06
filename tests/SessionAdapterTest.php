<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Config\Services;
use CodeIgniter\Test\CIDatabaseTestCase;
use Fluent\Auth\Adapters\SessionAdapter;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Facades\Auth;
use Fluent\Auth\Models\UserModel;

class SessionAdapterTest extends CIDatabaseTestCase implements AuthenticationTestInterface
{
    /** @var AuthFactoryInterface|AuthenticationInterface */
    protected $auth;

    /** @var string */
    protected $namespace = '\Fluent\Auth';

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = Auth::guard('web');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Services::reset();
    }

    public function testAuthenticate()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->auth->authenticate();

        $user = fake(UserModel::class);

        $this->auth->login($user);

        $this->assertNotNull($this->auth->authenticate());
        $this->assertEquals($user, $this->auth->authenticate());
    }

    public function testAttempt()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'secret',
        ]);

        $this->assertTrue($result);
        $this->assertNotNull($this->auth->user());
    }

    public function testAttemptWithUsername()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->attempt([
            'username' => $user->username,
            'password' => 'secret',
        ]);

        $this->assertTrue($result);
        $this->assertNotNull($this->auth->user());
    }

    public function testFailedAttempt()
    {
        $user = fake(UserModel::class, [
            'password' => 'valid-password',
        ]);

        $result = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'secret',
        ]);

        $this->assertFalse($result);
    }

    public function testRememberAttempt()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'secret',
        ], true);

        $this->assertTrue($result);

        $this->seeInDatabase('users', [
            'remember_token' => $this->auth->user()->getRememberToken(),
        ]);

        $this->assertNotNull($this->auth->user()->getRememberToken());
    }

    public function testNotRememberAttempt()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'secret',
        ], false);

        $this->assertTrue($result);

        $this->assertNull($this->auth->user()->getRememberToken());
    }

    public function testFailedUserAttempt()
    {
        $result = $this->auth->attempt([
            'email'    => 'notfounduser@mail.com',
            'password' => 'password',
        ]);

        $this->assertFalse($result);
    }

    public function testValidate()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->validate([
            'email'    => $user->email,
            'password' => 'secret',
        ]);

        $this->assertTrue($result);
    }

    public function testFailedValidate()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->validate([
            'email'    => $user->email,
            'password' => 'not-valid-password',
        ]);

        $this->assertFalse($result);
    }

    public function testFailedUserValidate()
    {
        $result = $this->auth->validate([
            'email'    => 'notfounduser@mail.com',
            'password' => 'secret',
        ]);

        $this->assertFalse($result);
    }

    public function testCheck()
    {
        $user = fake(UserModel::class);

        $this->assertFalse($this->auth->check());

        $this->auth->login($user);

        $this->assertTrue($this->auth->check());
    }

    public function testLogin()
    {
        $user = fake(UserModel::class);

        $this->auth->login($user);

        $this->assertTrue(session()->has($this->auth->getSessionName()));
        $this->assertEquals(session($this->auth->getSessionName()), $this->auth->id());
        $this->assertEquals($user->id, $this->auth->id());
    }

    public function testLoginById()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->loginById($user->id);

        $this->assertNotNull($result);
        $this->assertEquals($user, $result);
        $this->assertTrue(session()->has($this->auth->getSessionName()));
        $this->assertEquals(session($this->auth->getSessionName()), $this->auth->id());
        $this->assertEquals($user->id, $this->auth->id());
    }

    public function testFailedLoginById()
    {
        $result = $this->auth->loginById(123);

        $this->assertFalse($result);
        $this->assertNull(session($this->auth->getSessionName()));
        $this->assertEquals(session($this->auth->getSessionName()), $this->auth->id());
    }

    public function testLogout()
    {
        $user = fake(UserModel::class);
        $this->auth->login($user, true);

        $this->assertTrue(session()->has($this->auth->getSessionName()));

        $this->seeInDatabase('users', ['remember_token' => $this->auth->user()->getRememberToken()]);

        $this->auth->logout();

        $this->assertFalse(session()->has($this->auth->getSessionName()));
    }

    public function testUser()
    {
        $user = fake(UserModel::class);

        $this->assertNull($this->auth->user());

        $this->auth->login($user);

        $this->assertNotNull($this->auth->user());
    }

    public function testId()
    {
        $user = fake(UserModel::class);

        $this->assertNull($this->auth->id());

        $this->auth->login($user);

        $this->assertNotNull($this->auth->id());
    }

    public function testHasUser()
    {
        $user = fake(UserModel::class);

        $this->assertFalse($this->auth->hasUser());

        $this->auth->login($user);

        $this->assertTrue($this->auth->hasUser());
    }

    public function testSetUser()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->loginById($user->id);

        $setUser = $this->auth->setUser($result);

        $this->assertInstanceOf(SessionAdapter::class, $setUser);
        $this->assertInstanceOf(AuthenticatorInterface::class, $this->auth->user());
    }

    public function testGetProvider()
    {
        $this->assertInstanceOf(UserProviderInterface::class, $this->auth->getProvider());
    }

    public function testSetProvider()
    {
        $setProvider = $this->auth->setProvider($this->auth->getProvider());

        $this->assertInstanceOf(SessionAdapter::class, $setProvider);
    }
}
