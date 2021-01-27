<?php

namespace Fluent\Auth\Tests;

interface AuthenticationTestInterface
{
    /**
     * Determine if current user is authenticated. If not, throw an exception.
     */
    public function testAuthenticate();

    /**
     * Attempts to authenticate a user with the given $credentials.
     */
    public function testAttempt();

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     */
    public function testRememberAttempt();

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     */
    public function testNotRememberAttempt();

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     */
    public function testFailedAttempt();

    /**
     * Attempts to authenticate a user with the given $credentials.
     * Logs the user in with a successful check.
     */
    public function testFailedUserAttempt();

    /**
     * Validate a user's credentials.
     */
    public function testValidate();

    /**
     * Validate a user's credentials.
     */
    public function testFailedValidate();

    /**
     * Validate a user's credentials.
     */
    public function testFailedUserValidate();

    /**
     * Checks if the user is currently logged in.
     */
    public function testCheck();

    /**
     * Logs the given user in.
     */
    public function testLogin();

    /**
     * Logs a user in based on their ID.
     */
    public function testLoginById();

    /**
     * Logs a user in based on their ID.
     */
    public function testFailedLoginById();

    /**
     * Logs the current user out.
     *
     * @return void
     */
    public function testLogout();

    /**
     * Returns the currently logged in user.
     */
    public function testUser();

    /**
     * Get the ID for the currently authenticated user.
     */
    public function testId();

    /**
     * Determine if the adapter has a user instance.
     */
    public function testHasUser();

    /**
     * Set the current user.
     *
     * @return $this
     */
    public function testSetUser();

    /**
     * Get the user provider used by the guard.
     */
    public function testGetProvider();

    /**
     * Set the user provider used by the guard.
     *
     * @return $this
     */
    public function testSetProvider();
}
