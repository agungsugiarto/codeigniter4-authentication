<?php

namespace Fluent\Auth\Tests;

interface AuthenticationTestInterface
{
    public function testAuthenticate();

    public function testAttempt();

    public function testAttemptWithUsername();

    public function testRememberAttempt();

    public function testNotRememberAttempt();

    public function testFailedAttempt();

    public function testFailedUserAttempt();

    public function testValidate();

    public function testFailedValidate();

    public function testFailedUserValidate();

    public function testCheck();

    public function testLogin();

    public function testLoginById();

    public function testFailedLoginById();

    public function testLogout();

    public function testUser();

    public function testId();

    public function testHasUser();

    public function testSetUser();

    public function testGetProvider();

    public function testSetProvider();
}
