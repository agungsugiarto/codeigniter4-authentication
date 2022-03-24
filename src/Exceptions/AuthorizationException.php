<?php

namespace Fluent\Auth\Exceptions;

use Exception;
use Throwable;
use Fluent\Auth\Authorization\Response;

class AuthorizationException extends Exception
{
    /**
     * The response from the gate.
     *
     * @var \Fluent\Auth\Authorization\Response
     */
    protected $response;

    /**
     * Create a new authorization exception instance.
     *
     * @param  string|null  $message
     * @param  mixed  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($message ?? 'This action is unauthorized.', 0, $previous);

        $this->code = $code ?: 403;
    }

    /**
     * Get the response from the gate.
     *
     * @return \Fluent\Auth\Authorization\Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Set the response from the gate.
     *
     * @param  \Fluent\Auth\Authorization\Response  $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Create a deny response object from this exception.
     *
     * @return \Fluent\Auth\Authorization\Response
     */
    public function toResponse()
    {
        return Response::deny($this->message, $this->code);
    }
}
