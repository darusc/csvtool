<?php

namespace Csvtool\Exceptions;

use Exception;

class InvalidActionException extends Exception
{
    public function __construct(private readonly string $action)
    {
        parent::__construct("Invalid action {$action}");
    }

    public function getAction(): string
    {
        return $this->action;
    }
}