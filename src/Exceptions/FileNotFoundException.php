<?php

namespace Csvtool\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct(string $filename)
    {
        parent::__construct("File $filename not found");
    }
}