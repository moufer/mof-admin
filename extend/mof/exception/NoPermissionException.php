<?php

namespace mof\exception;

use Throwable;

class NoPermissionException extends \LogicException
{
    public function __construct($message = "", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}