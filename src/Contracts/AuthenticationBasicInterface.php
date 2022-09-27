<?php

namespace Fluent\Auth\Contracts;

interface AuthenticationBasicInterface
{
    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @param  string  $field
     * @param  array  $extraConditions
     * @throws \Fluent\Auth\Exceptions\AuthenticationException
     */
    public function basic($field = 'email', $extraConditions = []);

    /**
     * Perform a stateless HTTP Basic login attempt.
     *
     * @param  string  $field
     * @param  array  $extraConditions
     * @throws \Fluent\Auth\Exceptions\AuthenticationException
     */
    public function onceBasic($field = 'email', $extraConditions = []);

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = []);
}