<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Exceptions\MetadataNotMatch;
use Csvtool\Models\CSVFile;
use Exception;

class MergeFilesCommand extends Command
{

    public static function getDefinition(): array
    {
        return [
            'name' => 'merge',
            'description' => 'Merge input files in the output file',
            'args' => ['inputs', 'output', '--index']
        ];
    }

    public function run(): void
    {
        try {
            $files = explode(',', $this->args["inputs"]);
            $inputs = [];
            // Open all the input files and check merge preconditions (same number of columns and matching headers)
            $header = [];
            $columns = -1;
            foreach ($files as $fileName) {
                $file = $this->fileService->open($fileName, CSVFile::MODE_READ);
                $inputs[] = $file;
                $cols = $file->getColumnCount();
                $h = $file->getHeader();

                // Stop if a file has a different number of columns than the rest
                if($columns !== -1 && $cols !== $columns) {
                    throw new Exception("File $fileName has different number of columns.");
                } else {
                    $columns = $cols; // update column count initial value
                }

                // Stop if a file has a different header
                if($header !== [] && $header !== $h) {
                    throw new Exception("File $fileName has different header.");
                } else {
                    $header = $h; // update initial header value
                }
            }

            $output = $this->fileService->open($this->args['output'], CSVFile::MODE_WRITE);
            $output->setHeader($header);

            // Merge all the input files by appending all the rows at the bottom of the output file
            $id = 1;
            foreach ($inputs as $input) {
                foreach($input->read() as $row) {
                    // Update the index column if the option was given and if it exists in the csv file
                    if(array_key_exists('index', $this->args) && array_key_exists($this->args['index'], $row)) {
                        $row[$this->args['index']] = $id++;
                    }
                    $output->write($row);
                }
            }

        } catch (Exception $exception) {
            $this->fileService->closeAll();
            throw $exception;
        }
    }
}