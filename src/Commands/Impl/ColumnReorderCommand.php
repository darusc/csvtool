<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Exception;

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
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            if(!$input->hasHeader()) {
                throw new Exception('Input file ' . $this->args['file'] . " doesn't have a header");
            }

            $oldHeader = $input->getHeader();
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

            $output->setHeader($ordered);
            foreach ($input->read() as $row) {
                // Create a new array that has the values in the new required order by mapping the
                // ordered array values to the row data. (The keys of the $row array are the column names)
                $output->write(
                    array_map(function ($item) use ($row) {
                        return $row[$item];
                    }, $ordered)
                );
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}