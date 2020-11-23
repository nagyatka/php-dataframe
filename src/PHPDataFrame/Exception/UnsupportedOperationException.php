<?php


namespace PHPDataFrame\Exception;


use Exception;
use Throwable;

class UnsupportedOperationException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        parent::__toString();
    }

    public function __wakeup()
    {
        parent::__wakeup();
    }

}