<?php

namespace Csvtool\Models;

use Csvtool\Exceptions\InvalidActionException;
use Generator;
use InvalidArgumentException;
use SplFileObject;

class CSVFile
{
    public const int MODE_READ = 0x01;
    public const int MODE_WRITE = 0x02;
    public const int MODE_TEMP = 0x03;

    private ?array $header = null;

    public function __construct(
        private readonly SplFileObject $splFileObject,
        private readonly int           $mode
    )
    {
        if ($mode & CSVFile::MODE_READ !== 0) {
            // Check if the file contains a header
            // If it contains numeric items it is a row entry and not the header
            // TODO: improve
            $this->header = $this->readRow();
            foreach ($this->header ?? [] as $item) {
                if (is_numeric($item)) {
                    $this->header = null;
                    break;
                }
            }

            // Rewind if the first row was not a header
            if ($this->header == null) {
                $this->splFileObject->rewind();
            }
        }
    }

    public function getHeader(): ?array
    {
        return $this->header;
    }

    public function hasHeader(): bool
    {
        return $this->header !== null;
    }

    public function getColumnCount(): int
    {
        return $this->header !== null ? count($this->header) : 0;
    }

    public function getFileSize(): int
    {
        return filesize($this->splFileObject->getRealPath());
    }

    public function rewind(): void
    {
        $this->splFileObject->rewind();
    }

    public function seek($line): void
    {
        $this->splFileObject->seek($line);
    }

    /**
     * Read a single row and advance.
     * Returns null if end of file
     * @throws InvalidActionException
     */
    public function readRow(): ?array
    {
        if ($this->mode & CSVFile::MODE_READ === 0) {
            throw new InvalidActionException("read (File was opened for writing)");
        }

        $row = $this->splFileObject->current();
        if ($row == [null] || $row === false) {
            return null;
        }

        $this->splFileObject->next();
        return $row;
    }

    /**
     * @throws InvalidActionException
     */
    public function read(): Generator
    {
        while (!$this->splFileObject->eof()) {
            $row = $this->readRow();

            if ($row === null) {
                break;
            }

            // If the file has a header map the read data
            // so the yielded row has the header information
            // otherwise yield directly the index based row
            if ($this->hasHeader()) {
                $mappedRow = [];
                foreach ($row as $index => $item) {
                    $mappedRow[$this->header[$index]] = $item;
                }
                yield $mappedRow;
            } else {
                yield $row;
            }
        }
    }

    /**
     * @throws InvalidActionException
     */
    public function setHeader(array $header): void
    {
        $this->header = $header;
        $this->write($header);
    }

    /**
     * @throws InvalidActionException
     */
    public function write(array $row): void
    {
        if ($this->mode & CSVFile::MODE_WRITE === 0) {
            throw new InvalidActionException("write (File was opened for reading)");
        }
        $this->splFileObject->fputcsv($row);
    }
}