<?php

namespace Fluent\Auth\Contracts;

interface PasswordBrokerFactoryInterface
{
    /**
     * Get a password broker instance by name.
     *
     * @param  string|null  $name
     * @return PasswordBrokerInterface
     */
    public function broker($name = null);

    /**
     * Get the default password broker name.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Set the default password broker name.
     *
     * @param  string  $name
     * @return $this
     */
    public function setDefaultDriver($name);
}
