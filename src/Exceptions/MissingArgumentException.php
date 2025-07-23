<?php

namespace Csvtool\Exceptions;

use Exception;

class MissingArgumentException extends Exception
{
    public function __construct(string $argument)
    {
        parent::__construct("Missing argument '$argument'");
    }
}