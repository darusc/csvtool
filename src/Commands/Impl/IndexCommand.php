<?php

namespace Csvtool\Commands\Impl;

use Csvtool\Commands\Command;
use Csvtool\Models\CSVFile;
use Exception;

class IndexCommand extends Command
{
    public static function getDefinition(): array
    {
        return [
            'name' => 'index',
            'description' => 'Add index column to CSV file',
            'args' => ['file', 'outfile']
        ];
    }

    public function run(): void
    {
        try {
            $input = $this->fileService->open($this->args['file'], CSVFile::MODE_READ);
            $output = $this->fileService->open($this->args['outfile'], CSVFile::MODE_WRITE);

            $header = $input->getHeader();
            $output->setHeader(['id', ...($header ?? [])]);

            $id = 1;
            foreach ($input->read() as $row) {
                $output->write([$id++, ...$row]);
            }

        } catch (Exception $ex) {
            $this->fileService->closeAll();
            echo $ex->getMessage();
        }
    }
}