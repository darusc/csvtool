<?php

namespace Csvtool\Exceptions;

use Exception;

class InvalidActionException extends Exception
{
    public function __construct(string $action)
    {
        parent::__construct("Invalid action: $action");
    }
}