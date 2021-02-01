<?php

namespace Fluent\Auth\Exceptions;

use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class AuthenticationException extends Exception
{
    /** @var string */
    protected $code = ResponseInterface::HTTP_FORBIDDEN;

    /** @return self */
    public static function forUnknownAdapter(string $adapter)
    {
        return new self(lang('Auth.unknownAdapter', [$adapter]));
    }

    /** @return self */
    public static function forUnknownUserProvider()
    {
        return new self(lang('Auth.unknownUserProvider'));
    }

    /** @return self */
    public static function forInvalidUser()
    {
        return new self(lang('Auth.invalidUser'));
    }
}
