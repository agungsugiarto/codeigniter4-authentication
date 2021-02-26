<?php

namespace Fluent\Auth\Traits;

trait AuthenticatableTrait
{
    /**
     * Returns the name of the column used to uniquely
     * identify this user, typically 'id'.
     */
    public function getAuthIdColumn(): string
    {
        return 'id';
    }

    /**
     * Returns the unique identifier of the object for
     * authentication purposes. Typically user's id.
     *
     * @return mixed
     */
    public function getAuthId()
    {
        return $this->attributes[$this->getAuthIdColumn()] ?? null;
    }

    /**
     * Returns the name of the column with the
     * email address of this user.
     */
    public function getAuthEmailColumn(): string
    {
        return 'email';
    }

    /**
     * Returns the email address for this user.
     *
     * @return string|null
     */
    public function getAuthEmail()
    {
        return $this->attributes[$this->getAuthEmailColumn()] ?? null;
    }

    /**
     * Get the password for the user.
     *
     * @return string|null
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Returns the "remember me" token for this user.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        $column = $this->getRememberColumn();

        // fix me sometime getter is not retrived.
        return $this->attributes[$column] ?? $this->{$column} ?? null;
    }

    /**
     * Sets the "remmeber me" token.
     *
     * @return self
     */
    public function setRememberToken(string $value)
    {
        $this->attributes[$this->getRememberColumn()] = $value;

        return $this;
    }

    /**
     * Returns the column name that stores the remember-me value.
     */
    public function getRememberColumn(): string
    {
        return 'remember_token';
    }
}
