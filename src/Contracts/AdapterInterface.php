<?php

namespace Fluent\Authentication\Contracts;

use Fluent\Authentication\Result;

interface AdapterInterface
{
    /**
     * Performs a authentication attempt.
     *
     * @return Result
     * @throws ExceptionInterface If authentication cannot be performed
     */
    public function attempt();
}
