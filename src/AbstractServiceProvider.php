<?php

namespace Fluent\Auth;

abstract class AbstractServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    abstract static function register();
}
