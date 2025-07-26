<?php

namespace Csvtool\Exceptions;

use Exception;

class InvalidFileOperation extends Exception
{
    public function __construct(string $file, string $action, string $details = "")
    {
        parent::__construct("Invalid $action on file $file. $details");
    }
}