<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIDatabaseTestCase;
use Fluent\Auth\Adapters\TokenAdapter;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthenticatorInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Contracts\UserProviderInterface;
use Fluent\Auth\Entities\AccessToken;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Models\UserModel;

class TokenAdapterTest extends CIDatabaseTestCase implements AuthenticationTestInterface
{
    /** @var AuthFactoryInterface|AuthenticationInterface */
    protected $auth;

    /** @var string */
    protected $namespace = '\Fluent\Auth';

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = Services::auth()->guard('token');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->auth->logout();
    }

    protected function setRequestHeader(string $token)
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Token ' . $token);
    }

    public function testAuthenticate()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.invalidUser'));

        $this->auth->authenticate();

        $user = fake(UserModel::class);

        $this->auth->login($user);

        $this->assertNotNull($this->auth->authenticate());
        $this->assertEquals($user, $this->auth->authenticate());
    }

    public function testAttempt()
    {
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('post', [
            'post:create',
            'post:read',
            'post:update',
            'post:delete',
        ]);

        $this->setRequestHeader($token->raw_token);

        $result = $this->auth->attempt([
            'token' => $token->raw_token,
        ]);

        $this->assertTrue($result);
        $this->assertNotNull($this->auth->user());
        $this->assertInstanceOf(AccessToken::class, $this->auth->user()->currentAccessToken());
        $this->assertEquals($token->token, $this->auth->user()->currentAccessToken()->token);

        $this->assertTrue($this->auth->user()->tokenCan('post:create'));
        $this->assertTrue($this->auth->user()->tokenCan('post:read'));
        $this->assertTrue($this->auth->user()->tokenCan('post:update'));
        $this->assertTrue($this->auth->user()->tokenCan('post:delete'));
        $this->assertFalse($this->auth->user()->tokenCant('post:create'));
        $this->assertFalse($this->auth->user()->tokenCant('post:read'));
        $this->assertFalse($this->auth->user()->tokenCant('post:update'));
        $this->assertFalse($this->auth->user()->tokenCant('post:delete'));
    }

    public function testAttemptWithUsername()
    {
        // not implemented
        $this->assertTrue(true);
    }

    public function testRememberAttempt()
    {
        // not implemented
        $this->assertTrue(true);
    }

    public function testNotRememberAttempt()
    {
        // not implemented
        $this->assertTrue(true);
    }

    public function testFailedAttempt()
    {
        $this->setRequestHeader('not-found-token');

        $result = $this->auth->attempt([
            'token' => 'not-found-token',
        ]);

        $this->assertFalse($result);
        $this->assertNull($this->auth->user());
    }

    public function testFailedUserAttempt()
    {
        // not implemented
        $this->assertTrue(true);
    }

    public function testValidate()
    {
        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $result1 = $this->auth->validate([
            'token' => $token->raw_token,
        ]);

        $this->assertTrue($result1);
    }

    public function testFailedValidate()
    {
        $result2 = $this->auth->validate([
            'token' => 'not-found-token',
        ]);

        $this->assertFalse($result2);
    }

    public function testFailedUserValidate()
    {
        // not implemented
        $this->assertTrue(true);
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

        $this->assertEquals($user->id, $this->auth->id());
    }

    public function testLoginById()
    {
        $user = fake(UserModel::class);

        $result = $this->auth->loginById($user->id);

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $this->auth->id());
    }

    public function testFailedLoginById()
    {
        $result = $this->auth->loginById(123);

        $this->assertFalse($result);
    }

    public function testLogout()
    {
        $user = fake(UserModel::class);
        $this->auth->login($user);

        $this->assertTrue($this->auth->check());

        $this->auth->logout();

        $this->assertFalse($this->auth->check());
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

        $this->assertInstanceOf(TokenAdapter::class, $setUser);
        $this->assertInstanceOf(AuthenticatorInterface::class, $this->auth->user());
    }

    public function testGetProvider()
    {
        $this->assertInstanceOf(UserProviderInterface::class, $this->auth->getProvider());
    }

    public function testSetProvider()
    {
        $setProvider = $this->auth->setProvider($this->auth->getProvider());

        $this->assertInstanceOf(TokenAdapter::class, $setProvider);
    }
}
