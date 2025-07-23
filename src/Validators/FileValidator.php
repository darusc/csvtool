<?php

namespace Csvtool\Validators;

use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;

class FileValidator
{
    /**
     * @throws FileNotFoundException
     * @throws FilePermissionException
     */
    public static function validate(string $filename): void
    {
        if(!file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        if(!is_readable($filename)) {
            throw new FilePermissionException($filename);
        }
    }
}