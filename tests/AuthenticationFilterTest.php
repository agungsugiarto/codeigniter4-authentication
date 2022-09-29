<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Config\Factories;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Exception;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\AuthenticationInterface;
use Fluent\Auth\Contracts\AuthFactoryInterface;
use Fluent\Auth\Exceptions\AuthenticationException;
use Fluent\Auth\Filters\AuthenticationBasicFilter;
use Fluent\Auth\Filters\AuthenticationFilter;
use Fluent\Auth\Models\UserModel;

class AuthenticationFilterTest extends CIDatabaseTestCase
{
    use FeatureTestTrait;

    /** @var AuthFactoryInterface|AuthenticationInterface */
    protected $auth;

    /** @var RouteCollection */
    protected $routes;

    /** @var string */
    protected $namespace = '\Fluent\Auth';

    /** @var string */
    protected $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = Services::auth();

        $filters                  = config('Filters');
        $filters->aliases['auth'] = AuthenticationFilter::class;
        $filters->aliases['auth.basic'] = AuthenticationBasicFilter::class;
        Factories::injectMock('filters', 'filters', $filters);

        $routes = Services::routes();
        $routes->group('secret', ['filter' => "auth:{$this->guard}"], function ($routes) {
            $routes->get('treasure', function () {
                return 'you found gems';
            });
        });

        $routes->group('basic', ['filter' => "auth.basic:web,email,basic"], function ($routes) {
            $routes->get('treasure', function () {
                return 'you found gems';
            });
        });
        
        $routes->group('onceBasic', ['filter' => "auth.basic:web,email,onceBasic"], function ($routes) {
            $routes->get('treasure', function () {
                return 'you found gems';
            });
        });

        Services::injectMock('routes', $routes);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Services::reset();
    }

    protected function setRequestHeader(string $token)
    {
        $request = service('request');
        $request->setHeader('Authorization', 'Token ' . $token);
    }

    public function testBasicFilter()
    {
        $user = fake(UserModel::class);

        $result = $this
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$user->email}:secret"),
            ])
            ->call('get', 'basic/treasure');

        $result->assertOK();
        $result->assertSee('you found gems');
        $this->assertNotNull($this->auth->user());
        $this->assertTrue(session()->has($this->auth->getSessionName()));
        $this->assertEquals(session($this->auth->getSessionName()), $this->auth->id());
    }

    public function testFailedBasicFilter()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $user = fake(UserModel::class);

        $result = $this
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$user->email}:secrets"),
            ])
            ->call('get', 'basic/treasure');

        $result->assertNotOK();
        $result->assertDontSee('you found gems');
        $result->assertHeader('WWW-Authenticate', 'Basic');
        $this->assertNull($this->auth->user());
    }

    public function testOnceBasicFilter()
    {
        $user = fake(UserModel::class);

        $result = $this
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$user->email}:secret"),
            ])
            ->call('get', 'onceBasic/treasure');

        $result->assertOK();
        $result->assertSee('you found gems');
        $this->assertNotNull($this->auth->user());
        $this->assertFalse(session()->has($this->auth->getSessionName()));
        $this->assertNotEquals(session($this->auth->getSessionName()), $this->auth->id());
    }

    public function testFailedOnceBasicFilter()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $user = fake(UserModel::class);

        $result = $this
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$user->email}:secrets"),
            ])
            ->call('get', 'onceBasic/treasure');

        $result->assertNotOK();
        $result->assertDontSee('you found gems');
        $result->assertHeader('WWW-Authenticate', 'Basic');
        $this->assertNull($this->auth->user());
    }

    public function testDefaultGuardFilter()
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->call('get', 'secret/treasure');

        $user = fake(UserModel::class);

        $login = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'secret',
        ]);

        $this->assertTrue($login);

        $result = $this->call('get', 'secret/treasure');

        $this->assertEquals('you found gems', $result);
        $this->assertNotNull($this->auth->user());
        $this->assertNotNull(session($this->auth->getSessionName()));
    }

    public function testSessionAuthenticate()
    {
        $this->guard = 'web';

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->call('get', 'secret/treasure');

        $user = fake(UserModel::class);

        $login = $this->auth->attempt([
            'email'    => $user->email,
            'password' => 'secret',
        ]);

        $this->assertTrue($login);

        $result = $this->call('get', 'secret/treasure');

        $this->assertEquals('you found gems', $result);
        $this->assertNotNull($this->auth->user());
        $this->assertNotNull(session($this->auth->getSessionName()));
    }

    public function testTokenGuardAuthenticate()
    {
        $this->guard = 'token';

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->call('get', 'secret/treasure');

        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $login = $this->auth->guard('token')->attempt([
            'token' => $token->raw_token,
        ]);

        $this->assertTrue($login);

        $result = $this->call('get', 'secret/treasure');

        $this->assertEquals('you found gems', $result);
        $this->assertNotNull($this->auth->guard('token')->user());
    }

    public function testTokenGuardAuthenticateHeader()
    {
        $this->guard = 'token';

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->call('get', 'secret/treasure');

        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');
        $this->setRequestHeader($token->raw_token);

        $result = $this->call('get', 'secret/treasure');

        $this->assertEquals('you found gems', $result);
        $this->assertNotNull($this->auth->guard('token')->user());
    }

    public function testTokenGuardAuthenticateQueryString()
    {
        $this->guard = 'token';

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthenticated.');

        $this->call('get', 'secret/treasure');

        $user  = fake(UserModel::class);
        $token = $user->generateAccessToken('foo');

        $result = $this->call('get', "secret/treasure?token={$token->raw_token}");

        $this->assertEquals('you found gems', $result);
        $this->assertNotNull($this->auth->guard('token')->user());
    }
}
