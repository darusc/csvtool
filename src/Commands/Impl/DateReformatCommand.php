<?php

namespace Csvtool\Commands\Impl;

use Carbon\Carbon;
use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Csvtool\Validators\DateValidator;
use DateTime;
use Exception;
use InvalidArgumentException;

class DateReformatCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'refdate',
            'description' => 'Reformat datetime column values using given format',
            'args' => ['file', 'format', 'outfile']
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

            foreach ($input->read() as $row) {
                $output->write(
                    array_map(function ($value) use ($format) {
                        // If the current value is a valid datetime reformat it with the new specified format
                        if (DateValidator::isValidDateTime($value)) {
                            return Carbon::parse($value)->format($format);
                        } else {
                            return $value;
                        }
                    }, $row)
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}