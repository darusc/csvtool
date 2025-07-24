<?php

namespace Csvtool\Exceptions;

use Exception;

class InvalidKeyException extends Exception
{
    public function __construct()
    {
        parent::__construct("Invalid key");
    }
}