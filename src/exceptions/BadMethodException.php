<?php
namespace Src\Exceptions;

use Throwable;
use Override;

class BadMethodException extends QueryException{
    public function __construct(string $message = "This method is not allowed ")
    {
        return parent::__construct($message, 401);
    }
}