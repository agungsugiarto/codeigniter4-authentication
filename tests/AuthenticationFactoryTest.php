<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use Fluent\Auth\Adapters\SessionAdapter;
use Fluent\Auth\AuthenticationFactory;
use Fluent\Auth\Config\Auth;
use Fluent\Auth\Exceptions\AuthenticationException;

class AuthenticationFactoryTest extends CIUnitTestCase
{
    /** @var AuthenticationFactory */
    protected $auth;

    public function setUp(): void
    {
        parent::setUp();

        $this->auth = new AuthenticationFactory(new Auth());
    }

    public function testThrowsOnUnknownAdapter()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage(lang('Auth.unknownAdapter', ['foo']));

        $this->auth->factory('foo');
    }

    public function testFactoryLoadsDefault()
    {
        $adapter1 = $this->auth->factory();
        $adapter2 = $this->auth->factory('default');

        $this->assertInstanceOf(SessionAdapter::class, $adapter1);
        $this->assertSame($adapter1, $adapter2);
    }

    public function testFactorySessionAdapter()
    {
        $session = $this->auth->factory('session');

        $this->assertInstanceOf(SessionAdapter::class, $session);
    }

    // public function testFactoryTokenAdapter()
    // {
    //     $token = $this->auth->factory('token');

    //     $this->assertInstanceOf(TokenAdapter::class, $token);
    // }
}
