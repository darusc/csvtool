<?php

namespace Csvtool\Commands\Impl;

use Carbon\Carbon;
use Csvtool\Commands\Command;
use Csvtool\Validators\DateValidator;
use DateTime;

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
        if($this->reader->open($this->args['file'])) {
            if($this->writer->open($this->args['outfile'])) {
                if($this->reader->hasHeader()) {
                    $this->writer->write($this->reader->getHeader());
                }

                $format = $this->args['format'];
                if(!DateValidator::isValidFormat($format)) {
                    echo "Specified format '$format' is not a valid date format." . PHP_EOL;
                    return;
                }

                foreach ($this->reader->read() as $row) {
                    $this->writer->write(
                        array_map(function ($value) use ($format) {
                            // If the current value is a valid datetime reformat it with the new specified format
                            if(DateValidator::isValidDateTime($value)) {
                                return Carbon::parse($value)->format($format);
                            } else {
                                return $value;
                            }
                        }, $row)
                    );
                }
            }
        }
    }
}