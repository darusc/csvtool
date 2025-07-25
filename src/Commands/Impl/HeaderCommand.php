<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Exceptions\FileNotFoundException;
use Csvtool\Exceptions\FilePermissionException;
use Csvtool\Models\CSVFile;
use Exception;

final class HeaderCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'header',
            'description' => 'Prepend CSV file with a header row',
            'args' => ['file', 'header', 'outfile']
        ];
    }

    public function run(): void
    {
        try {
            $header = explode(',', $this->args['header']);

            // Open the input file
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);

            // Open the output file and write the header
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            // Write the header and copy the contents
            $output->setHeader($header);
            foreach ($input->read() as $row) {
                $output->write($row);
            }

        } catch (Exception $ex) {
            $this->fileService->closeAll();
            echo $ex->getMessage();
        }
    }
}