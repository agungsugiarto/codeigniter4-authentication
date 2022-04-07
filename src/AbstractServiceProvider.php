<?php

namespace Fluent\Auth;

use Fluent\Auth\Facades\Gate;

abstract class AbstractServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected static $policies = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    abstract static function register();

    /**
     * Register the application's policies.
     *
     * @return void
     */
    public static function registerPolicies()
    {
        foreach (static::policies() as $key => $value) {
            Gate::policy($key, $value);
        }
    }

    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public static function policies()
    {
        return static::$policies;
    }
}
