<?php

namespace Fluent\Auth\Exceptions;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class AuthenticationException extends Exception
{
    /** @var string */
    protected $code = ResponseInterface::HTTP_FORBIDDEN;

    public static function forUnknownHandler(string $handler)
    {
        return new self(lang('auth.unknownHandler', [$handler]));
    }

    public static function forUnknownUserProvider()
    {
        return new self(lang('auth.unknownUserProvider'));
    }

    public static function forInvalidUser()
    {
        return new self(lang('auth.invalidUser'));
    }
}
