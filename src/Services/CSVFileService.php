<?php

namespace Csvtool\Services;

use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;
use Csvtool\Models\CSVFile;
use Csvtool\Validators\FileValidator;
use SplFileObject;

/**
 * Service to manage opening and closing of CSV files.
 */
class CSVFileService
{
    /** @var CSVFile[] */
    private array $files = [];

    /**
     * @throws FilePermissionException
     * @throws FileNotFoundException
     */
    public function open(string $filename, int $mode): CSVFile
    {
        if (isset($this->files[$filename])) {
            return $this->files[$filename];
        }

        if ($mode === CSVFile::MODE_READ) {
            FileValidator::validate($filename);
            $splObject = new SplFileObject($filename, 'r');
            $splObject->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        } else {
            $splObject = new SplFileObject($filename, 'w');
            $splObject->setCsvControl(",", "\"", "\\");
        }

        $file = new CSVFile($splObject, $mode);
        $this->files[$filename] = $file;

        return $file;
    }

    public function close(string $filename): void
    {
        unset($this->files[$filename]);
    }

    public function closeAll(): void
    {
        $this->files = [];
    }
}