<?php

namespace Fluent\Auth;

use Fluent\Auth\Facades\Gate;
use Fluent\Auth\AbstractServiceProvider;

class AuthServiceProvider extends AbstractServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected static $policies = [];

    /**
     * {@inheritdoc}
     */
    public static function register()
    {
        static::registerPolicies();
    }
}
