<?php

namespace mof\exception;

use Throwable;

class AuthTokenException extends \RuntimeException
{
    public function __construct($message = "", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}