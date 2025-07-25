<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Exception;

class ColumnTruncateCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'truncate',
            'description' => 'Truncate string column values to given length',
            'args' => ['file', 'length', 'outfile', '--ellipsis']
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
                echo 'Argument "length" must be an integer' . PHP_EOL;
                return;
            }

            foreach ($input->read() as $row) {
                $output->write(
                    array_map(function ($value) use ($length) {
                        // Truncate string values to given length and ignore the rest
                        // and add ... if option was specified
                        if (is_string($value) && !is_numeric($value)) {
                            if (array_key_exists('ellipsis', $this->args)) {
                                return substr($value, 0, $length) . '..';
                            } else {
                                return substr($value, 0, $length);
                            }
                        } else {
                            return $value;
                        }
                    }, $row)
                );
            }

        } catch (Exception $ex) {
            $this->fileService->closeAll();
            echo $ex->getMessage() . PHP_EOL;
        }
    }
}