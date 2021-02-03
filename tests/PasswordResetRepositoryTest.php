<?php

namespace Fluent\Auth\Tests;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIDatabaseTestCase;
use Fluent\Auth\Contracts\PasswordResetRepositoryInterface;
use Fluent\Auth\Models\UserModel;
use Fluent\Auth\Passwords\PasswordResetRepository;

class PasswordResetRepositoryTest extends CIDatabaseTestCase
{
    /** @var string */
    protected $namespace = 'Fluent\Auth';

    /** @var PasswordResetRepositoryInterface */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new PasswordResetRepository();
    }

    public function testCreateResetToken()
    {
        $user = fake(UserModel::class);

        $create = $this->repository->create($user);

        $this->assertIsString($create);

        $this->seeInDatabase('auth_password_resets', [
            'email' => $user->email,
        ]);
    }

    public function testCreateNewToken()
    {
        $token = $this->repository->createNewToken();

        $this->assertIsString($token);
    }

    public function testTokenExists()
    {
        $user = fake(UserModel::class);

        $token = $this->repository->create($user);

        $result1 = $this->repository->exists($user, $token);
        $result2 = $this->repository->exists($user, 'not-valid-token');

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function testRecentlyCreatedToken()
    {
        $user = fake(UserModel::class);

        $result1 = $this->repository->recentlyCreatedToken($user);

        $this->repository->create($user);
        $result2 = $this->repository->recentlyCreatedToken($user);

        $this->assertFalse($result1);

        $this->assertTrue($result2);
    }

    public function testDestroy()
    {
        $user = fake(UserModel::class);

        $this->repository->create($user);
        $this->repository->destroy($user);

        $this->dontSeeInDatabase('auth_password_resets', [
            'email' => $user->email,
        ]);
    }

    public function testDestroyExpired()
    {
        $this->db->table('auth_password_resets')->insert([
            'email'      => 'random@mail.com',
            'token'      => 'random-token',
            'created_at' => Time::now()->subSeconds(120 * 120),
        ]);

        $this->seeInDatabase('auth_password_resets', [
            'email' => 'random@mail.com',
        ]);

        $this->repository->destroyExpired();

        $this->dontSeeInDatabase('auth_password_resets', [
            'email' => 'random@mail.com',
        ]);
    }
}
