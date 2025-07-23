<?php

namespace Csvtool\Services;

use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;
use Csvtool\Validators\FileValidator;
use Generator;
use SplFileObject;

class StreamReaderService implements CSVReaderInterface
{
    private SplFileObject $splFileObject;
    private ?array $header = null;

    public function open(string $filename): bool
    {
        try {
            FileValidator::validate($filename);

            $this->splFileObject = new SplFileObject($filename);
            $this->splFileObject->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

            // Check if the file contains a header
            // If it contains numeric items it is a row entry and not the header
            // TODO: improve
            $this->header = $this->splFileObject->current();
            foreach ($this->header as $item) {
                if(is_numeric($item)) {
                    $this->header = null;
                    break;
                }
            }

            return true;
        } catch (FileNotFoundException|FilePermissionException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function hasHeader(): bool
    {
        return $this->header !== null;
    }

    public function getHeader(): ?array
    {
        return $this->header;
    }

    public function read(): Generator
    {
        // Skip the header row if it exists
        if($this->hasHeader()) {
            $this->splFileObject->next();
        }

        while (!$this->splFileObject->eof()) {
            $data = $this->splFileObject->current();

            if($data == [null] || $data === false) {
                break;
            }

            // If the file has a header map the read data
            // so the yielded row has the header information
            // otherwise yield directly the index based row
            if($this->hasHeader()) {
                $row = [];
                foreach ($data as $index => $item) {
                    $row[$this->header[$index]] = $item;
                }
                yield $row;
            } else {
                yield $data;
            }

            $this->splFileObject->next();
        }
    }
}