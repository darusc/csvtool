<?php

namespace Csvtool\Services;

use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;
use Csvtool\Validators\FileValidator;
use SplFileObject;

class FIleWriterService implements CSVWriterInterface
{
    private SplFileObject $splFileObject;

    public function open(string $filename, ?array $header = null): bool
    {
        try {
            FileValidator::validate($filename);

            $this->splFileObject = new SplFileObject($filename, 'w');
            $this->splFileObject->setCsvControl(",", "\"", "\\");

            // Write the header if present
            if($header !== null) {
                $this->splFileObject->fputcsv($header);
            }

            return true;
        } catch (FileNotFoundException|FilePermissionException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function write(array $row): void
    {
        $this->splFileObject->fputcsv($row);
    }
}