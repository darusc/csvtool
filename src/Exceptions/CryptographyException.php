<?php

namespace Csvtool\Exceptions;

use Exception;

class CryptographyException extends Exception
{
    public function __construct(string $message, string $opensslerror = "")
    {
        parent::__construct($message . " " . $opensslerror);
    }
}