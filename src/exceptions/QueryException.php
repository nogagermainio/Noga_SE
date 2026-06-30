<?php
namespace Src\Exceptions;

use Exception;
use Throwable;

class QueryException extends Exception{
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }

    public function status():int{
        return $this->code;
    }
}