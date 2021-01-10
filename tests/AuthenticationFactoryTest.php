<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use Fluent\Auth\Adapters\SessionAdapter;
use Fluent\Auth\Adapters\TokenAdapter;
use Fluent\Auth\AuthenticationFactory;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Models\UserModel;

class AuthenticationFactoryTest extends CIUnitTestCase
{
    /** @var AuthenticationFactory */
    protected $auth;

    public function setUp(): void
    {
        parent::setUp();

        $this->auth = (new AuthenticationFactory(new Auth()))->setProvider(new UserModel());
    }

    public function testThrowsOnUnknownHandler()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.unknownHandler', ['foo']));

        $this->auth->factory('foo');
    }

    public function testFactoryLoadsDefault()
    {
        $shield1 = $this->auth->factory();
        $shield2 = $this->auth->factory('default');

        $this->assertInstanceOf(SessionAdapter::class, $shield1);
        $this->assertSame($shield1, $shield2);
    }

    public function testFactorySessionAdapter()
    {
        $session = $this->auth->factory('session');

        $this->assertInstanceOf(SessionAdapter::class, $session);
    }

    public function testFactoryTokenAdapter()
    {
        $token = $this->auth->factory('token');

        $this->assertInstanceOf(TokenAdapter::class, $token);
    }
}
