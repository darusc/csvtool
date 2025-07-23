<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;

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
        if($this->reader->open($this->args['file'])) {
            if($this->writer->open($this->args['outfile'])) {
                if($this->reader->hasHeader()) {
                    $this->writer->write($this->reader->getHeader());
                }

                $length = $this->args['length'];
                if(!ctype_digit($length)) {
                    echo 'Argument "length" must be an integer' . PHP_EOL;
                    return;
                }

                foreach ($this->reader->read() as $row) {
                    $this->writer->write(
                        array_map(function ($value) use ($length) {
                            // Truncate string values to given length and ignore the rest
                            // and add ... if option was specified
                            if(is_string($value) && !is_numeric($value)) {
                                if(array_key_exists('ellipsis', $this->args)) {
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
            }
        }
    }
}