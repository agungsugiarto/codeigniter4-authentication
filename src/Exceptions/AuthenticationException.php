<?php

namespace Fluent\Auth\Exceptions;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class AuthenticationException extends Exception
{
    /** @var string */
    protected $code = ResponseInterface::HTTP_FORBIDDEN;

    public static function forUnknownAdapter(string $adapter)
    {
        return new self(lang('Auth.unknownAdapter', [$adapter]));
    }

    public static function forUnknownUserProvider()
    {
        return new self(lang('Auth.unknownUserProvider'));
    }

    public static function forInvalidUser()
    {
        return new self(lang('Auth.invalidUser'));
    }
}
