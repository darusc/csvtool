<?php

namespace Csvtool\Exceptions;

use Exception;

class MetadataNotMatch extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct("CSV file metadata doesn't match. $message");
    }
}