<?php

namespace Csvtool\Commands\Impl;

use Carbon\Carbon;
use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Csvtool\Validators\DateValidator;
use Exception;
use InvalidArgumentException;

class DateReformatCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'refdate',
            'description' => 'Reformat datetime column values using given format',
            'args' => ['file', 'column', 'format', 'outfile']
        ];
    }

    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            if ($input->hasHeader()) {
                $output->write($input->getHeader());
            }

            $format = $this->args['format'];
            if (!DateValidator::isValidFormat($format)) {
                throw new InvalidArgumentException("Specified format '$format' is not a valid date format.");
            }

            $column = $this->args['column'];
            if (!in_array($column, $input->getHeader())) {
                throw new InvalidArgumentException("Column '$column' not found in file.");
            }

            foreach ($input->read() as $row) {
                $output->write(
                    array_map(function ($key) use ($format, $column, $row) {
                        // If the current value is a valid datetime reformat it with the new specified format
                        if ($key === $column && DateValidator::isValidDateTime($row[$key])) {
                            return Carbon::parse($row[$key])->format($format);
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