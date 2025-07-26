<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Exception;
use InvalidArgumentException;

class ColumnTruncateCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'truncate',
            'description' => 'Truncate string column values to given length',
            'args' => ['file', 'column', 'length', 'outfile', '--ellipsis']
        ];
    }

    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            if ($input->hasHeader()) {
                $output->setHeader($input->getHeader());
            }

            $length = $this->args['length'];
            if (!ctype_digit($length)) {
                throw new InvalidArgumentException('Argument "length" must be an integer');
            }

            $column = $this->args['column'];
            if (!in_array($column, $input->getHeader())) {
                throw new InvalidArgumentException("Column '$column' not found in file.");
            }

            foreach ($input->read() as $row) {
                $output->write(
                    array_map(function ($key) use ($row, $column, $length) {
                        if($key !== $column) {
                            return $row[$key];
                        }

                        // Truncate string values to given length and ignore the rest
                        // and add ... if option was specified
                        if (is_string($row[$key]) && !is_numeric($row[$key])) {
                            if (array_key_exists('ellipsis', $this->args)) {
                                return substr($row[$key], 0, $length) . '..';
                            } else {
                                return substr($row[$key], 0, $length);
                            }
                        } else {
                            return $row[$key];
                        }
                    }, array_keys($row))
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}