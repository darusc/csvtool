<?php

namespace Csvtool\Exceptions;

use Exception;

class InvalidFileException extends Exception
{
    public function __construct(string $file)
    {
        parent::__construct("Invalid file '$file'");
    }
}