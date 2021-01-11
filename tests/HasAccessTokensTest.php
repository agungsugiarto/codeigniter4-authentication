<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIDatabaseTestCase;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Models\UserModel;

class HasAccessTokensTest extends CIDatabaseTestCase
{
    protected $namespace = 'Fluent\Auth';

    /** @var \Fluent\Auth\Entities\User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = fake(UserModel::class);
    }

    public function testGenerateToken()
	{
		$token = $this->user->generateAccessToken('foo');

		$this->assertEquals('foo', $token->name);
		$this->assertNull($token->expires);

		// When newly created, should have the raw, plain-text token as well
		// so it can be shown to users.
		$this->assertNotNull($token->raw_token);
		$this->assertEquals($token->token, hash('sha256', $token->raw_token));

		// All scopes are assigned by default via wildcard
		$this->assertEquals(['*'], $token->scopes);
	}

	public function testGetAccessToken()
	{
		// Should return null when not found
		$this->assertNull($this->user->getAccessToken('foo'));

		$token = $this->user->generateAccessToken('foo');

		$found = $this->user->getAccessToken($token->raw_token);

		$this->assertEquals($token->id, $found->id);
	}

	public function testGetAccessTokenById()
	{
		// Should return null when not found
		$this->assertNull($this->user->getAccessTokenById(123));

		$token = $this->user->generateAccessToken('foo');
		$found = $this->user->getAccessTokenById($token->id);

		$this->assertEquals($token->id, $found->id);
	}

	public function testRevokeAccessToken()
	{
		$token = $this->user->generateAccessToken('foo');

		$this->assertCount(1, $this->user->accessTokens());

		$this->user->revokeAccessToken($token->raw_token);

		$this->assertCount(0, $this->user->accessTokens());
	}

	public function testRevokeAllAccessTokens()
	{
		$this->user->generateAccessToken('foo');
		$this->user->generateAccessToken('foo');

		$this->assertCount(2, $this->user->accessTokens());

		$this->user->revokeAllAccessTokens();

		$this->assertCount(0, $this->user->accessTokens());
	}

	public function testTokenCanNoTokenSet()
	{
		$this->assertFalse($this->user->tokenCan('foo'));
	}

	public function testTokenCanBasics()
	{
		$token = $this->user->generateAccessToken('foo', ['foo:bar']);
		$this->user->withAccessToken($token);

		$this->assertTrue($this->user->tokenCan('foo:bar'));
		$this->assertFalse($this->user->tokenCan('foo:baz'));
	}

	public function testTokenCantNoTokenSet()
	{
		$this->assertTrue($this->user->tokenCant('foo'));
	}

	public function testTokenCant()
	{
		$token = $this->user->generateAccessToken('foo', ['foo:bar']);
		$this->user->withAccessToken($token);

		$this->assertFalse($this->user->tokenCant('foo:bar'));
		$this->assertTrue($this->user->tokenCant('foo:baz'));
    }
    
    public function testMultipeToken()
    {
        $token = $this->user->generateAccessToken('post', [
            'post:create', 'post:read', 'post:update', 'post:delete',
        ]);

        $this->user->withAccessToken($token);

        $this->assertTrue($this->user->tokenCan('post:create'));
        $this->assertTrue($this->user->tokenCan('post:read'));
        $this->assertTrue($this->user->tokenCan('post:update'));
        $this->assertTrue($this->user->tokenCan('post:delete'));
        $this->assertFalse($this->user->tokenCant('post:create'));
        $this->assertFalse($this->user->tokenCant('post:read'));
        $this->assertFalse($this->user->tokenCant('post:update'));
        $this->assertFalse($this->user->tokenCant('post:delete'));
    }
}