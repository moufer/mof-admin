<?php

namespace mof\exception;

use Throwable;

class LogicException extends \LogicException
{
    public function __construct($message = "", $code = 2, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}