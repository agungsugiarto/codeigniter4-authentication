<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIDatabaseTestCase;
use Fluent\Auth\Models\AccessTokenModel;

class AccessTokenTest extends CIDatabaseTestCase
{
    protected $namespace = 'Fluent\Auth';

    public function testCanNoScopes()
    {
        $token = fake(AccessTokenModel::class);

        $this->assertFalse($token->can('foo'));
    }

    public function testCanWildcard()
    {
        $token = fake(AccessTokenModel::class, ['scopes' => ['*']]);

        $this->assertTrue($token->can('foo'));
        $this->assertTrue($token->can('bar'));
    }

    public function testCanSuccess()
    {
        $token = fake(AccessTokenModel::class, ['scopes' => ['foo']]);

        $this->assertTrue($token->can('foo'));
        $this->assertFalse($token->can('bar'));
    }

    public function testCantNoScopes()
    {
        $token = fake(AccessTokenModel::class);

        $this->assertTrue($token->cant('foo'));
    }

    public function testCantWildcard()
    {
        $token = fake(AccessTokenModel::class, ['scopes' => ['*']]);

        $this->assertFalse($token->cant('foo'));
        $this->assertFalse($token->cant('bar'));
    }

    public function testCantSuccess()
    {
        $token = fake(AccessTokenModel::class, ['scopes' => ['foo']]);

        $this->assertFalse($token->cant('foo'));
        $this->assertTrue($token->cant('bar'));
    }

    public function testScopeMultiple()
    {
        $token = fake(AccessTokenModel::class, [
            'scopes' => ['post:create', 'post:read', 'post:update', 'post:delete'],
        ]);

        $this->assertTrue($token->can('post:create'));
        $this->assertTrue($token->can('post:read'));
        $this->assertTrue($token->can('post:update'));
        $this->assertTrue($token->can('post:delete'));
        $this->assertFalse($token->cant('post:create'));
        $this->assertFalse($token->cant('post:read'));
        $this->assertFalse($token->cant('post:update'));
        $this->assertFalse($token->cant('post:delete'));
    }
}
