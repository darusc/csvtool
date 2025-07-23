<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;

class ColumnReorderCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'reorder',
            'description' => 'Reorder columns in CSV file based on given column sequence. Flag --removenotinseq removes columns that are not in the given sequence.',
            'args' => ['file', 'sequence', 'outfile', '--removenotinseq']
        ];
    }

    public function run(): void
    {
        // Open the input file
        if($this->reader->open($this->args['file'])) {

            if(!$this->reader->hasHeader()) {
                echo 'Input file ' . $this->args['file'] . " doesn't have a header" . PHP_EOL;
                return;
            }

            $oldHeader = $this->reader->getHeader();
            $sequence = explode(',', $this->args['sequence']);

            // Create a new array containing the existing header columns in the required order
            // The reference sequence may contain additional columns
            $ordered = array_filter($sequence, function ($item) use ($oldHeader) {
                return in_array($item, $oldHeader);
            });

            // Append the columns that were not found in the reference sequence at the end
            // if not disabled by the removenotinseq option
            if(!array_key_exists('removenotinseq', $this->args)) {
                foreach ($oldHeader as $value) {
                    if(!in_array($value, $ordered)) {
                        $ordered[] = $value;
                    }
                }
            }

            if($this->writer->open($this->args['outfile'])) {
                // Write the new header
                $this->writer->write($ordered);

                foreach ($this->reader->read() as $row) {
                    // Create a new array that has the values in the new required order by mapping the
                    // ordered array values to the row data. (The keys of the $row array are the column names)
                    $this->writer->write(
                        array_map(function ($item) use ($row) {
                            return $row[$item];
                        }, $ordered)
                    );
                }
            }
        }
    }
}