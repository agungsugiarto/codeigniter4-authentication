<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use Fluent\Auth\Config\Services;
use Fluent\Auth\Facades\Hash;
use Fluent\Auth\Passwords\Hash\Argon2IdHasher;
use Fluent\Auth\Passwords\Hash\ArgonHasher;
use Fluent\Auth\Passwords\Hash\BcryptHasher;
use RuntimeException;

use function defined;
use function password_get_info;

class HasherTest extends CIUnitTestCase
{
    public function testBasicBcryptHashing()
    {
        $hasher = new BcryptHasher();
        $value  = $hasher->make('password');
        $this->assertNotSame('password', $value);
        $this->assertTrue($hasher->check('password', $value));
        $this->assertFalse($hasher->needsRehash($value));
        $this->assertTrue($hasher->needsRehash($value, ['rounds' => 1]));
        $this->assertSame('bcrypt', password_get_info($value)['algoName']);
    }

    public function testBasicArgon2iHashing()
    {
        if (! defined('PASSWORD_ARGON2I')) {
            $this->markTestSkipped('PHP not compiled with Argon2i hashing support.');
        }

        $hasher = new ArgonHasher();
        $value  = $hasher->make('password');
        $this->assertNotSame('password', $value);
        $this->assertTrue($hasher->check('password', $value));
        $this->assertFalse($hasher->needsRehash($value));
        $this->assertTrue($hasher->needsRehash($value, ['threads' => 1]));
        $this->assertSame('argon2i', password_get_info($value)['algoName']);
    }

    public function testBasicArgon2idHashing()
    {
        if (! defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('PHP not compiled with Argon2id hashing support.');
        }

        $hasher = new Argon2IdHasher();
        $value  = $hasher->make('password');
        $this->assertNotSame('password', $value);
        $this->assertTrue($hasher->check('password', $value));
        $this->assertFalse($hasher->needsRehash($value));
        $this->assertTrue($hasher->needsRehash($value, ['threads' => 1]));
        $this->assertSame('argon2id', password_get_info($value)['algoName']);
    }

    /**
     * @depends testBasicBcryptHashing
     */
    public function testBasicBcryptVerification()
    {
        $this->expectException(RuntimeException::class);

        if (! defined('PASSWORD_ARGON2I')) {
            $this->markTestSkipped('PHP not compiled with Argon2i hashing support.');
        }

        $argonHasher = new ArgonHasher(['verify' => true]);
        $argonHashed = $argonHasher->make('password');
        (new BcryptHasher(['verify' => true]))->check('password', $argonHashed);
    }

    /**
     * @depends testBasicArgon2iHashing
     */
    public function testBasicArgon2iVerification()
    {
        $this->expectException(RuntimeException::class);

        $bcryptHasher = new BcryptHasher(['verify' => true]);
        $bcryptHashed = $bcryptHasher->make('password');
        (new ArgonHasher(['verify' => true]))->check('password', $bcryptHashed);
    }

    /**
     * @depends testBasicArgon2idHashing
     */
    public function testBasicArgon2idVerification()
    {
        $this->expectException(RuntimeException::class);

        $bcryptHasher = new BcryptHasher(['verify' => true]);
        $bcryptHashed = $bcryptHasher->make('password');
        (new Argon2IdHasher(['verify' => true]))->check('password', $bcryptHashed);
    }

    public function testWithLoadFromService()
    {
        $hasher = Services::hash()->driver('bcrypt');
        $value  = $hasher->make('password');
        $this->assertNotSame('password', $value);
        $this->assertTrue($hasher->check('password', $value));
        $this->assertFalse($hasher->needsRehash($value));
        $this->assertTrue($hasher->needsRehash($value, ['rounds' => 1]));
        $this->assertSame('bcrypt', password_get_info($value)['algoName']);
    }

    public function testWithLoadFromStaticMethod()
    {
        $value = Hash::driver('bcrypt')->make('password');
        $this->assertNotSame('password', $value);
        $this->assertTrue(Hash::driver('bcrypt')->check('password', $value));
        $this->assertFalse(Hash::driver('bcrypt')->needsRehash($value));
        $this->assertTrue(Hash::driver('bcrypt')->needsRehash($value, ['rounds' => 1]));
        $this->assertSame('bcrypt', password_get_info($value)['algoName']);
    }
}
