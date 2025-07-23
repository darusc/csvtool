<?php

namespace Csvtool\Services;

use Exception;
use SplFileObject;

class FIleWriterService implements CSVWriterInterface
{
    private SplFileObject $splFileObject;

    public function open(string $filename, ?array $header = null): bool
    {
        try {
            $this->splFileObject = new SplFileObject($filename, 'w');
            $this->splFileObject->setCsvControl(",", "\"", "\\");

            // Write the header if present
            if ($header !== null) {
                $this->splFileObject->fputcsv($header);
            }

            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function write(array $row): void
    {
        $this->splFileObject->fputcsv($row);
    }
}