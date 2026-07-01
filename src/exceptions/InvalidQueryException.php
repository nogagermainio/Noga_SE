<?php
namespace Noga\Exceptions;

use Throwable;
use Override;

class InvalidQueryException extends QueryException{
    
    public function __construct(string $message = "")
    {
        return parent::__construct($message, 402);
    }
}