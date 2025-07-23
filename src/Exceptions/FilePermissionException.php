<?php

namespace Csvtool\Exceptions;

use Exception;

class FilePermissionException extends Exception
{
    public function __construct(string $file)
    {
        parent::__construct("Permission error: $file");
    }
}